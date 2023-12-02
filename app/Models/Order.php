<?php

namespace App\Models;

use App\Traits\Discountable;
use App\Traits\Localizable;
use App\Traits\Priceable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, Localizable, Priceable, Discountable;

    protected $guarded = ['id'];
    protected $casts = ['paid' => 'boolean'];

    /**
     * @return HasMany
     */
    public function orderLines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderable()
    {
        return $this->morphTo();
    }
}
