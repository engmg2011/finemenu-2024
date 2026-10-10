<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A code a customer can type. The discount rules (value, dates, scope, usage type)
 * live on the parent Coupon; this row only tracks the code and how often it was used.
 */
class CouponCode extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'redeemed_count' => 'integer',
    ];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    /**
     * How many times this code may be redeemed, or null for unlimited.
     * single → 1, multi → coupon.usage_limit (null = unlimited).
     */
    public function usageLimit(): ?int
    {
        return $this->coupon->usage_type === 'single' ? 1 : $this->coupon->usage_limit;
    }
}
