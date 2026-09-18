<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active'              => 'boolean',
        'discount_value'         => 'float',
        'redeemed_count'         => 'integer',
        'usage_limit'            => 'integer',
        'usage_start_date'       => 'date',
        'usage_end_date'         => 'date',
        'reservation_start_date' => 'date',
        'reservation_end_date'   => 'date',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    /**
     * Check if a given user has already redeemed this coupon.
     */
    public function isRedeemedByUser(int $userId): bool
    {
        return $this->redemptions()->where('user_id', $userId)->exists();
    }

    /**
     * Check if the coupon is currently valid.
     *
     * Two independent date constraints are enforced:
     *
     *  1. USAGE window (usage_start_date / usage_end_date) — always checked.
     *     Today's date must fall within this window.  This is the period
     *     during which the coupon may be redeemed at all.
     *
     *  2. RESERVATION window (reservation_start_date / reservation_end_date) — optional.
     *     When these columns are set on the coupon the order's reservation
     *     dates must fall within them.  Concrete rules:
     *       - If the coupon has reservation dates but NO reservation is
     *         supplied → invalid (coupon is only for reservations).
     *       - If the coupon has NO reservation dates → any (or no) reservation
     *         is accepted; only the usage window is checked.
     *
     * @param string|null $reservationStart  Y-m-d of the reservation start date
     * @param string|null $reservationEnd    Y-m-d of the reservation end date
     */
    public function isValid(?string $reservationStart = null, ?string $reservationEnd = null): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // 1. Usage window: today must be within usage_start_date … usage_end_date
        $today      = now()->toDateString();
        $usageStart = $this->usage_start_date->toDateString();
        $usageEnd   = $this->usage_end_date->toDateString();

        if ($today < $usageStart || $today > $usageEnd) {
            return false;
        }

        // 2. Reservation window (only when the coupon defines one)
        $couponResStart = $this->reservation_start_date
            ? $this->reservation_start_date->toDateString()
            : null;
        $couponResEnd   = $this->reservation_end_date
            ? $this->reservation_end_date->toDateString()
            : null;

        if ($couponResStart !== null || $couponResEnd !== null) {
            // Coupon requires a reservation — reject if none is provided
            if ($reservationStart === null && $reservationEnd === null) {
                return false;
            }

            $resStart = $reservationStart ?? $reservationEnd;
            $resEnd   = $reservationEnd   ?? $reservationStart;

            // Reservation start must be on or after the coupon's reservation_start_date
            if ($couponResStart !== null && $resStart < $couponResStart) {
                return false;
            }

            // Reservation end must be on or before the coupon's reservation_end_date
            if ($couponResEnd !== null && $resEnd > $couponResEnd) {
                return false;
            }
        }

        // 3. Usage limits
        if ($this->usage_type === 'single' && $this->redeemed_count >= 1) {
            return false;
        }

        if ($this->usage_type === 'multi'
            && $this->usage_limit !== null
            && $this->redeemed_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Calculate the discount amount for a given subtotal.
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($this->discount_type === 'percentage') {
            return round($subtotal * ($this->discount_value / 100), 3);
        }

        return min($this->discount_value, $subtotal);
    }
}
