<?php

namespace App\Traits;

use App\Models\Addon;

trait Addonable{
    public function addons()
    {
        return $this->morphMany(Addon::class, 'addonable');
    }
}
