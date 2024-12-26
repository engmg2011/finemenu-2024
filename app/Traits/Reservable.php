<?php

namespace App\Traits;

use App\Models\Reservation;

trait Reservable
{

    public function reservations() {
        return $this->morphMany(Reservation::class, 'reservable');
    }

}
