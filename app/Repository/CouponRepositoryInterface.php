<?php

namespace App\Repository;

use App\Models\Coupon;
use App\Models\CouponCode;

interface CouponRepositoryInterface
{
    public function list();
    public function get(int $id): ?Coupon;
    public function findCode(string $code): ?CouponCode;
    public function createModel(array $data): Coupon;
    public function updateModel(int $id, array $data): Coupon;
    public function destroy(int $id): bool;
    public function applyCoupon(string $code, float $subtotal, int $userId, int $businessId, ?int $branchId = null, ?string $reservationStart = null, ?string $reservationEnd = null): array;

    /** Reserve the code for an unpaid order. Aborts with 422 if it is no longer available. */
    public function holdCode(int $couponCodeId, int $userId, int $orderId): void;

    /** Renew/re-acquire the order's hold before payment. False = block the payment. */
    public function holdForPayment(int $orderId): bool;

    /** Count the code as used once the order is paid. Never throws. */
    public function confirmRedemption(int $orderId): void;

    /** Generate $count random codes "{code_prefix}-{RANDOM}" for a coupon. Returns how many were created. */
    public function generateCodes(Coupon $coupon, int $count): int;

    /** Paginated codes of a coupon. Optional request filter: redeemed=1|0. */
    public function listCodes(int $couponId);
}
