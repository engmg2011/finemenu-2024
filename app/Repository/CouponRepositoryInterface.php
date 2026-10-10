<?php

namespace App\Repository;

use App\Models\Coupon;

interface CouponRepositoryInterface
{
    public function list();
    public function get(int $id): ?Coupon;
    public function getByCode(string $code): ?Coupon;
    public function createModel(array $data): Coupon;
    public function updateModel(int $id, array $data): Coupon;
    public function destroy(int $id): bool;
    public function applyCoupon(string $code, float $subtotal, int $userId, int $businessId, ?int $branchId = null, ?string $reservationStart = null, ?string $reservationEnd = null): array;
    public function recordRedemption(int $couponId, int $userId, int $orderId): void;
}
