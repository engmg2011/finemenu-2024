<?php

namespace App\Models\Items\Restaurant;

use App\Traits\Featurable;
use App\Traits\Itemable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantProduct extends Model
{
    use HasFactory, Itemable, Featurable;
    protected $guarded=['id'];
    protected $casts = ['amount'=>'integer'];
    public $timestamps = false;
}
