<?php

namespace App\Models;

use App\Traits\Addonable;
use App\Traits\Discountable;
use App\Traits\Priceable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLine extends Model
{
    use HasFactory, Priceable, Addonable, Discountable;
    protected $guarded = ['id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function item() {
       return $this->belongsTo(Item::class);
    }

}
