<?php

namespace App\Repository\Eloquent;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Repository\CouponRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class CouponRepository extends BaseRepository implements CouponRepositoryInterface
{
    public function __construct(Coupon $model)
    {
        parent::__construct($model);
    }

    protected function process(array $data): array
    {
        return array_only($data, [
            'code', 'discount_type', 'discount_value',
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

        return Coupon::with(['business', 'branch'])
            ->when($businessId, fn($q) => $q->where('business_id', $businessId))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('id')
            ->paginate(request('per-page', 15));
    }

    public function get(int $id): ?Coupon
    {
        return Coupon::with(['business', 'branch'])->find($id);
    }

    public function getByCode(string $code): ?Coupon
    {
        return Coupon::where('code', $code)->first();
    }

    public function createModel(array $data): Coupon
    {
        return Coupon::create($this->process($data));
    }

    public function updateModel(int $id, array $data): Coupon
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update($this->process($data));
        return $coupon->fresh();
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
     * Validate and apply a coupon code, returning a coupon snapshot and the discount amount.
     * Does NOT write a redemption record — that happens after payment succeeds.
     *
     * @param string      $code              The coupon code to validate.
     * @param float       $subtotal          Order subtotal (pass 0 for an early pre-check).
     * @param int         $userId            Authenticated user.
     * @param int         $businessId        Business the order belongs to.
     * @param int|null    $branchId          Branch the order belongs to (if any).
     * @param string|null $reservationStart  Y-m-d of the earliest reservation start in the order.
     * @param string|null $reservationEnd    Y-m-d of the latest reservation end in the order.
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
        $coupon = $this->getByCode($code);

        if (!$coupon) {
            abort(422, 'Coupon code not found.');
        }

        if ((int) $coupon->business_id !== $businessId) {
            abort(422, 'Coupon does not belong to this business.');
        }

        // If the coupon is scoped to a specific branch, the order branch must match
        if ($coupon->branch_id !== null) {
            if ($branchId === null || (int) $coupon->branch_id !== $branchId) {
                abort(422, 'Coupon is not valid for this branch.');
            }
        }

        if (!$coupon->isValid($reservationStart, $reservationEnd)) {
            // Give a specific message when the failure is about reservation dates
            $hasResCoupon = $coupon->reservation_start_date !== null || $coupon->reservation_end_date !== null;
            if ($hasResCoupon && $reservationStart === null && $reservationEnd === null) {
                abort(422, 'This coupon is only valid when applied to a reservation.');
            }
            if ($hasResCoupon && ($reservationStart !== null || $reservationEnd !== null)) {
                abort(422, 'Coupon is not valid for the selected reservation dates.');
            }
            abort(422, 'Coupon is not valid or has expired.');
        }

        if ($coupon->isRedeemedByUser($userId)) {
            abort(422, 'You have already used this coupon.');
        }

        // When subtotal is 0 this is an early validation call (before order lines are saved).
        // The real discount_amount will be recalculated by the caller once the subtotal is known.
        $discountAmount = $subtotal > 0 ? $coupon->calculateDiscount($subtotal) : 0;

        return [
            'coupon_id'       => $coupon->id,
            'code'            => $coupon->code,
            'discount_type'   => $coupon->discount_type,
            'discount_value'  => $coupon->discount_value,
            'discount_amount' => $discountAmount,
            'business_id'     => $coupon->business_id,
            'branch_id'       => $coupon->branch_id,
        ];
    }

    /**
     * Record a redemption for a coupon after a successful payment.
     */
    public function recordRedemption(int $couponId, int $userId, int $orderId): void
    {
        // Idempotent: only record once per user per coupon
        $existing = CouponRedemption::where('coupon_id', $couponId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            return;
        }

        CouponRedemption::create([
            'coupon_id' => $couponId,
            'user_id'   => $userId,
            'order_id'  => $orderId,
        ]);

        Coupon::where('id', $couponId)->increment('redeemed_count');
    }
}
