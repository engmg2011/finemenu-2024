<?php

namespace App\Traits;

use App\Models\Event;

trait Eventable{
    public function prices()
    {
        return $this->morphMany(Event::class, 'eventable');
    }
}
