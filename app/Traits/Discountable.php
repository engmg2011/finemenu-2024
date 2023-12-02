<?php

namespace App\Traits;

use App\Models\Discount;

trait Discountable{
    public function discounts()
    {
        return $this->morphMany(Discount::class, 'discountable');
    }
}
