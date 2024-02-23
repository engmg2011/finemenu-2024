<?php

namespace App\Models;

use App\Traits\Discountable;
use App\Traits\Localizable;
use App\Traits\Mediable;
use App\Traits\Priceable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory, Localizable, Priceable, Mediable, Discountable;
    protected $guarded = ['id'];
    protected $hidden = ['updated_at' , 'created_at'];

    public function items(){
        return $this->belongsToMany(Item::class, 'plan_item');
    }
}
