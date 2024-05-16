<?php

namespace App\Models;

use App\Traits\Localizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{
    use HasFactory, Localizable;

    protected $guarded = ['id'];
    public $timestamps = false;

    public function tables()
    {
        return $this->hasMany(Table::class);
    }
}
