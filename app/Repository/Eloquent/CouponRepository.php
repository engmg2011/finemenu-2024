<?php

namespace App\Repository\Eloquent;

use App\Models\Coupon;
use App\Models\CouponCode;
use App\Models\CouponRedemption;
use App\Models\Order;
use App\Repository\CouponRepositoryInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class CouponRepository extends BaseRepository implements CouponRepositoryInterface
{
    /** Random part alphabet: uppercase letters and digits without look-alikes (0/O, 1/I). */
    protected const CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    /** 32^4 ≈ 1.05 million combinations per prefix — protected by the failed-attempt limits below. */
    protected const RANDOM_LENGTH = 4;

    /** Rows inserted per query when generating codes. */
    protected const INSERT_CHUNK = 500;

    /** Failed coupon attempts allowed per user per window. */
    protected const MAX_FAILED_ATTEMPTS = 6;
    protected const FAILED_ATTEMPTS_WINDOW_SECONDS = 60;

    /** Daily cap on failed attempts per user — stops slow guessing that stays under the per-minute limit. */
    protected const MAX_FAILED_ATTEMPTS_PER_DAY = 20;
    protected const FAILED_ATTEMPTS_DAY_SECONDS = 86400;

    /** How long an unpaid order reserves its coupon code. Renewed when the customer opens checkout. */
    protected const HOLD_MINUTES = 30;

    public function __construct(Coupon $model)
    {
        parent::__construct($model);
    }

    protected function process(array $data): array
    {
        return array_only($data, [
            'code_prefix', 'discount_type', 'discount_value',
            'usage_type', 'usage_limit', 'is_active',
            'usage_start_date', 'usage_end_date',
            'reservation_start_date', 'reservation_end_date',
            'business_id', 'branch_id',
        ]);
    }

    public function list()
    {
        $businessId = request()->route('businessId');
        $branchId   = request()->route('branchId');

        return Coupon::with(['business', 'branch', 'firstCode'])
            ->withCount('codes')
            ->when($businessId, fn($q) => $q->where('business_id', $businessId))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('id')
            ->paginate(request('per-page', 15));
    }

    public function get(int $id): ?Coupon
    {
        return Coupon::with(['business', 'branch', 'firstCode'])->withCount('codes')->find($id);
    }

    /**
     * Case-insensitive lookup: codes are stored uppercase and input is normalized the same way.
     */
    public function findCode(string $code): ?CouponCode
    {
        return CouponCode::with('coupon')->where('code', self::normalizeCode($code))->first();
    }

    /**
     * Creates the coupon and its codes:
     *  - `code` given                     → one code with that value (manual coupon)
     *  - `code_prefix` + `codes_count`    → that many generated "{PREFIX}-{RANDOM}" codes
     *  - `code_prefix` only               → no codes yet (generate later via generateCodes)
     *  - nothing                          → one random code (previous default behavior)
     */
    public function createModel(array $data): Coupon
    {
        $coupon = DB::transaction(function () use ($data) {
            $coupon = Coupon::create($this->process($data));

            if (!empty($data['code'])) {
                $coupon->codes()->create(['code' => self::normalizeCode($data['code'])]);
            } elseif (!empty($data['code_prefix'])) {
                if (!empty($data['codes_count'])) {
                    $this->generateCodes($coupon, (int) $data['codes_count']);
                }
            } else {
                $coupon->codes()->create(['code' => $this->uniqueRandomCode(null)]);
            }

            return $coupon;
        });

        return $this->get($coupon->id);
    }

    public function updateModel(int $id, array $data): Coupon
    {
        $coupon = Coupon::withCount('codes')->findOrFail($id);

        // Existing codes were generated with the old prefix — changing it would leave them inconsistent.
        if (array_key_exists('code_prefix', $data)
            && $coupon->code_prefix !== null
            && self::normalizeCode((string) $data['code_prefix']) !== $coupon->code_prefix
            && $coupon->codes_count > 0) {
            abort(422, 'The code prefix cannot be changed after codes have been generated.');
        }

        DB::transaction(function () use ($coupon, $data) {
            $coupon->update($this->process($data));

            // Renaming a code only makes sense for a coupon that has exactly one.
            if (!empty($data['code'])) {
                if ($coupon->codes_count !== 1) {
                    abort(422, 'The code can only be changed on a coupon that has a single code.');
                }
                $coupon->codes()->update(['code' => self::normalizeCode($data['code'])]);
            }
        });

        return $this->get($id);
    }

    public function destroy(int $id): bool
    {
        $coupon = Coupon::find($id);
        if (!$coupon) {
            return false;
        }
        return (bool) $coupon->delete();
    }

    /**
     * Validate a code for a user and return a snapshot with the discount amount.
     * Does NOT reserve the code — see holdCode().
     *
     * Every failed attempt counts toward per-user limits (MAX_FAILED_ATTEMPTS per minute and
     * MAX_FAILED_ATTEMPTS_PER_DAY per day) to stop code guessing. This runs for both the check-code endpoint and order creation,
     * so the limit cannot be bypassed by submitting guesses as orders.
     */
    public function applyCoupon(
        string  $code,
        float   $subtotal,
        int     $userId,
        int     $businessId,
        ?int    $branchId = null,
        ?string $reservationStart = null,
        ?string $reservationEnd = null
    ): array {
        $limiterKey = 'coupon-failed-attempts:' . $userId;
        $dailyKey   = 'coupon-failed-attempts-daily:' . $userId;

        if (RateLimiter::tooManyAttempts($limiterKey, self::MAX_FAILED_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($limiterKey);
            abort(429, "Too many invalid coupon attempts. Please try again in {$seconds} seconds.");
        }
        if (RateLimiter::tooManyAttempts($dailyKey, self::MAX_FAILED_ATTEMPTS_PER_DAY)) {
            $minutes = (int) ceil(RateLimiter::availableIn($dailyKey) / 60);
            abort(429, "Too many invalid coupon attempts today. Please try again in {$minutes} minutes.");
        }

        $couponCode = $this->findCode($code);
        if (!$couponCode) {
            $this->reject($userId, 'Coupon code not found.');
        }

        $coupon = $couponCode->coupon;

        if ((int) $coupon->business_id !== $businessId) {
            $this->reject($userId, 'Coupon does not belong to this business.');
        }

        // If the coupon is scoped to a specific branch, the order branch must match
        if ($coupon->branch_id !== null && ($branchId === null || (int) $coupon->branch_id !== $branchId)) {
            $this->reject($userId, 'Coupon is not valid for this branch.');
        }

        if (!$coupon->isValid($reservationStart, $reservationEnd)) {
            // Give a specific message when the failure is about reservation dates
            $hasResCoupon = $coupon->reservation_start_date !== null || $coupon->reservation_end_date !== null;
            if ($hasResCoupon && $reservationStart === null && $reservationEnd === null) {
                $this->reject($userId, 'This coupon is only valid when applied to a reservation.');
            }
            if ($hasResCoupon && ($reservationStart !== null || $reservationEnd !== null)) {
                $this->reject($userId, 'Coupon is not valid for the selected reservation dates.');
            }
            $this->reject($userId, 'Coupon is not valid or has expired.');
        }

        if (!$this->hasCapacityFor($couponCode, $userId)) {
            $this->reject($userId, 'This coupon code has already been used.');
        }

        // One paid use per user per coupon — e.g. an employee cannot use two ZAIN20 codes.
        if ($coupon->isRedeemedByUser($userId)) {
            $this->reject($userId, 'You have already used this coupon.');
        }

        // Note: the failure counter is intentionally NOT cleared on success, otherwise a user
        // holding one valid code could reset it between guesses.

        // When subtotal is 0 this is an early validation call (before order lines are saved).
        // The real discount_amount will be recalculated by the caller once the subtotal is known.
        $discountAmount = $subtotal > 0 ? $coupon->calculateDiscount($subtotal) : 0;

        return [
            'coupon_id'       => $coupon->id,
            'coupon_code_id'  => $couponCode->id,
            'code'            => $couponCode->code,
            'discount_type'   => $coupon->discount_type,
            'discount_value'  => $coupon->discount_value,
            'discount_amount' => $discountAmount,
            'business_id'     => $coupon->business_id,
            'branch_id'       => $coupon->branch_id,
        ];
    }

    /**
     * Reserve the code for an unpaid order (called inside the order-creation transaction).
     *
     * The code row is locked, so two orders racing for the last use of a code are serialized:
     * the second sees the first one's hold and is rejected. A user has at most one hold per
     * coupon; creating a new order moves it to that order (e.g. after a failed payment).
     */
    public function holdCode(int $couponCodeId, int $userId, int $orderId): void
    {
        DB::transaction(function () use ($couponCodeId, $userId, $orderId) {
            $couponCode = CouponCode::with('coupon')->lockForUpdate()->findOrFail($couponCodeId);
            $existing   = $this->userRedemption($couponCode->coupon_id, $userId);

            if ($existing?->isConfirmed()) {
                abort(422, 'You have already used this coupon.');
            }
            if (!$this->hasCapacityFor($couponCode, $userId)) {
                abort(422, 'This coupon code has already been used.');
            }

            $this->saveHold($existing, $couponCode, $userId, $orderId);
        });
    }

    /**
     * Called right before sending the customer to the payment gateway.
     * Renews the order's hold, or re-acquires it if it expired and the code is still free.
     * Returns false when the order must not be paid because its code is no longer available.
     */
    public function holdForPayment(int $orderId): bool
    {
        $order = Order::find($orderId);
        if (!$order || !$order->coupon_code_id) {
            return true;
        }

        return DB::transaction(function () use ($order) {
            $couponCode = CouponCode::with('coupon')->lockForUpdate()->find($order->coupon_code_id);
            if (!$couponCode) {
                return false;
            }

            $redemption = $this->userRedemption($couponCode->coupon_id, $order->user_id);

            if ($redemption?->isConfirmed()) {
                // Already used — fine only if it was used by this very order.
                return $redemption->order_id === $order->id;
            }

            // The user's hold is currently reserving the code for a different (newer) order.
            if ($redemption && $redemption->order_id !== $order->id && $redemption->isActiveHold()) {
                return false;
            }

            if (!$this->hasCapacityFor($couponCode, $order->user_id)) {
                return false;
            }

            $this->saveHold($redemption, $couponCode, $order->user_id, $order->id);
            return true;
        });
    }

    /**
     * Mark the order's coupon as used. Called when the order becomes paid.
     *
     * The money has already been taken at this point, so this never throws: if the code was
     * somehow used up in the meantime, the use is still recorded (the discount was given)
     * and a warning is logged for follow-up.
     */
    public function confirmRedemption(int $orderId): void
    {
        try {
            DB::transaction(function () use ($orderId) {
                $order = Order::find($orderId);
                if (!$order || !$order->coupon_code_id) {
                    return;
                }

                $couponCode = CouponCode::with('coupon')->lockForUpdate()->find($order->coupon_code_id);
                if (!$couponCode) {
                    return;
                }

                $redemption = $this->userRedemption($couponCode->coupon_id, $order->user_id);

                if ($redemption?->isConfirmed()) {
                    if ($redemption->order_id !== $order->id) {
                        Log::warning('Order paid with a coupon the user had already used', [
                            'order_id' => $order->id, 'coupon_code_id' => $couponCode->id,
                        ]);
                    }
                    return; // idempotent
                }

                $limit = $couponCode->usageLimit();
                if ($limit !== null && $couponCode->redeemed_count >= $limit) {
                    Log::warning('Coupon code used beyond its limit (hold expired before payment)', [
                        'order_id' => $order->id, 'coupon_code_id' => $couponCode->id, 'code' => $couponCode->code,
                    ]);
                }

                $attributes = [
                    'coupon_code_id' => $couponCode->id,
                    'order_id'       => $order->id,
                    'status'         => CouponRedemption::STATUS_CONFIRMED,
                    'expires_at'     => null,
                ];
                $redemption
                    ? $redemption->update($attributes)
                    : CouponRedemption::create($attributes + [
                        'coupon_id' => $couponCode->coupon_id,
                        'user_id'   => $order->user_id,
                    ]);

                CouponCode::whereKey($couponCode->id)->increment('redeemed_count');
                // Coupon-level total across all its codes (reporting only; limits are per code).
                Coupon::whereKey($couponCode->coupon_id)->increment('redeemed_count');
            });
        } catch (\Throwable $e) {
            Log::error('Failed to confirm coupon redemption', ['order_id' => $orderId, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Whether the code has a use left for this user: paid uses plus other users'
     * active holds must stay below the per-code limit.
     */
    protected function hasCapacityFor(CouponCode $couponCode, int $userId): bool
    {
        $limit = $couponCode->usageLimit();
        if ($limit === null) {
            return true;
        }

        $heldByOthers = CouponRedemption::where('coupon_code_id', $couponCode->id)
            ->where('status', CouponRedemption::STATUS_PENDING)
            ->where('expires_at', '>', now())
            ->where('user_id', '!=', $userId)
            ->count();

        return $couponCode->redeemed_count + $heldByOthers < $limit;
    }

    /** The user's single redemption row for a coupon (pending or confirmed), locked. */
    protected function userRedemption(int $couponId, int $userId): ?CouponRedemption
    {
        return CouponRedemption::where('coupon_id', $couponId)
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->first();
    }

    protected function saveHold(?CouponRedemption $existing, CouponCode $couponCode, int $userId, int $orderId): void
    {
        $attributes = [
            'coupon_code_id' => $couponCode->id,
            'order_id'       => $orderId,
            'status'         => CouponRedemption::STATUS_PENDING,
            'expires_at'     => now()->addMinutes(self::HOLD_MINUTES),
        ];

        try {
            $existing
                ? $existing->update($attributes)
                : CouponRedemption::create($attributes + ['coupon_id' => $couponCode->coupon_id, 'user_id' => $userId]);
        } catch (UniqueConstraintViolationException) {
            // Same user placing two orders with this coupon at the same moment.
            abort(422, 'You already have an order in progress with this coupon.');
        }
    }

    /**
     * Generate $count unique codes "{code_prefix}-{RANDOM}" for a coupon.
     * Candidates are built in memory, then checked against the DB with one query per chunk.
     * The unique index on coupon_codes.code is the final guard against races.
     */
    public function generateCodes(Coupon $coupon, int $count): int
    {
        if (empty($coupon->code_prefix)) {
            abort(422, 'Set a code prefix on the coupon before generating codes.');
        }

        return DB::transaction(function () use ($coupon, $count) {
            $created    = 0;
            $iterations = 0;

            while ($created < $count) {
                if (++$iterations > 100) {
                    abort(500, 'Unable to generate unique coupon codes. Please retry.');
                }

                $needed     = min(self::INSERT_CHUNK, $count - $created);
                $candidates = [];
                while (count($candidates) < $needed) {
                    $candidates[$coupon->code_prefix . '-' . $this->randomPart()] = true;
                }
                $candidates = array_keys($candidates);

                $taken = CouponCode::whereIn('code', $candidates)->pluck('code')->all();
                $fresh = array_values(array_diff($candidates, $taken));

                $now  = now()->toDateTimeString();
                $rows = array_map(fn($code) => [
                    'coupon_id'      => $coupon->id,
                    'code'           => $code,
                    'redeemed_count' => 0,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ], $fresh);

                CouponCode::insert($rows);
                $created += count($rows);
            }

            return $created;
        });
    }

    public function listCodes(int $couponId)
    {
        $redeemed = request('redeemed');

        return CouponCode::where('coupon_id', $couponId)
            ->when($redeemed !== null && $redeemed !== '', fn($q) => filter_var($redeemed, FILTER_VALIDATE_BOOLEAN)
                ? $q->where('redeemed_count', '>', 0)
                : $q->where('redeemed_count', 0))
            ->orderBy('id')
            ->paginate(request('per-page', 15));
    }

    public static function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
    }

    /** Counts the failed attempt toward the user's per-minute and daily limits, then rejects the request. */
    protected function reject(int $userId, string $message): never
    {
        RateLimiter::hit('coupon-failed-attempts:' . $userId, self::FAILED_ATTEMPTS_WINDOW_SECONDS);
        RateLimiter::hit('coupon-failed-attempts-daily:' . $userId, self::FAILED_ATTEMPTS_DAY_SECONDS);
        abort(422, $message);
    }

    /** A random code with an optional prefix that does not exist yet. */
    protected function uniqueRandomCode(?string $prefix): string
    {
        do {
            $code = ($prefix ? $prefix . '-' : '') . $this->randomPart();
        } while (CouponCode::where('code', $code)->exists());

        return $code;
    }

    protected function randomPart(): string
    {
        $max    = strlen(self::CODE_ALPHABET) - 1;
        $result = '';
        for ($i = 0; $i < self::RANDOM_LENGTH; $i++) {
            $result .= self::CODE_ALPHABET[random_int(0, $max)];
        }
        return $result;
    }
}
