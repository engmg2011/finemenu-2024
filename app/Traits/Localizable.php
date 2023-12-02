<?php


namespace App\Traits;


use App\Models\Locales;

trait Localizable
{
    public function locales() {
        return $this->morphMany(Locales::class, 'localizable');
    }


}
