<?php

namespace App\Traits;

use App\Models\Configuration;

trait Configurable{

    public function configurations()
    {
        return $this->morphMany(Configuration::class, 'configurable');
    }

    public function getConfig(string $key, $default = null)
    {
        return $this->configurations->where('key', $key)->first()->value ?? $default;
    }

    public function setConfig(string $key, $value)
    {
        return $this->configurations()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
