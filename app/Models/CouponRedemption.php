<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponRedemption extends Model
{
    use HasFactory;

    public const STATUS_PENDING   = 'pending';   // code held for an unpaid order
    public const STATUS_CONFIRMED = 'confirmed'; // order paid — the code was used

    protected $guarded = ['id'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /** A pending hold that still reserves the code. */
    public function isActiveHold(): bool
    {
        return $this->status === self::STATUS_PENDING && $this->expires_at?->isFuture();
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function couponCode(): BelongsTo
    {
        return $this->belongsTo(CouponCode::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
