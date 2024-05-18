<?php

namespace App\Models;

use App\Traits\Localizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Branch extends Model
{
    use HasFactory, Localizable;

    protected $guarded = ['id'];
    public $timestamps = false;

    public function menu(): HasOne
    {
        return $this->hasOne(Menu::class, 'menu_id');
    }
}
