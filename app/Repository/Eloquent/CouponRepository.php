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
            'start_date', 'end_date', 'business_id', 'branch_id',
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
     * Validate and apply a coupon code, returning coupon snapshot data and the discount amount.
     * Does NOT write a redemption record here — that is done after payment succeeds.
     */
    public function applyCoupon(string $code, float $subtotal, int $userId, int $businessId, ?int $branchId = null): array
    {
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

        if (!$coupon->isValid()) {
            abort(422, 'Coupon is not valid or has expired.');
        }

        if ($coupon->isRedeemedByUser($userId)) {
            abort(422, 'You have already used this coupon.');
        }

        $discountAmount = $coupon->calculateDiscount($subtotal);

        // Build a snapshot to cache with the order
        $snapshot = [
            'coupon_id'      => $coupon->id,
            'code'           => $coupon->code,
            'discount_type'  => $coupon->discount_type,
            'discount_value' => $coupon->discount_value,
            'discount_amount' => $discountAmount,
            'business_id'    => $coupon->business_id,
            'branch_id'      => $coupon->branch_id,
        ];

        return $snapshot;
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
