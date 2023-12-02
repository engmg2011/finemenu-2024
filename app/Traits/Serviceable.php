<?php

namespace App\Traits;

use App\Models\Service;

trait Serviceable{
    public function prices()
    {
        return $this->morphMany(Service::class, 'serviceable');
    }
}
