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
        'is_active'     => 'boolean',
        'discount_value' => 'float',
        'redeemed_count' => 'integer',
        'usage_limit'   => 'integer',
        'start_date'    => 'date',
        'end_date'      => 'date',
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
     * Check if the coupon is currently valid (active, within date range, usage not exceeded).
     */
    public function isValid(): bool
    {
        $today = now()->toDateString();

        if (!$this->is_active) {
            return false;
        }

        if ($today < $this->start_date->toDateString() || $today > $this->end_date->toDateString()) {
            return false;
        }

        if ($this->usage_type === 'single' && $this->redeemed_count >= 1) {
            return false;
        }

        if ($this->usage_type === 'multi' && $this->usage_limit !== null && $this->redeemed_count >= $this->usage_limit) {
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
