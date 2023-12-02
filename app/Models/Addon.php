<?php

namespace App\Models;

use App\Traits\Localizable;
use App\Traits\Mediable;
use App\Traits\Priceable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    use HasFactory, Priceable, Localizable, Mediable;
    protected $guarded = ['id'];

    public function children()
    {
        return $this->hasMany(Addon::class, 'parent_id');
    }
}
