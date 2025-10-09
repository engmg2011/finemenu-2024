<?php

namespace App\Models\Items;

use App\Traits\Featurable;
use App\Traits\Itemable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory, Itemable, Featurable;
    protected $guarded=['id'];
    protected $casts = ['units'=>'integer', 'unit_details' => 'array'];
    protected $hidden = ['created_at','updated_at'];

}
