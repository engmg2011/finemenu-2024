<?php

namespace App\Traits;

use App\Models\Price;

trait Priceable{
    public function prices()
    {
        return $this->morphMany(Price::class, 'priceable');
    }
}
