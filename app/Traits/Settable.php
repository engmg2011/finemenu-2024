<?php

namespace App\Traits;

use App\Models\Setting;

trait Settable
{
    public function settings() {
        return $this->morphMany(Setting::class, 'settable');
    }
}
