<?php

namespace App\Models\Items\Cars;

use App\Traits\Localizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarModel extends Model
{
    use HasFactory, Localizable;
    protected $guarded = ['id'];
    public $timestamps = false;
}
