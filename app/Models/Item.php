<?php

namespace App\Models;

use App\Traits\Addonable;
use App\Traits\Discountable;
use App\Traits\Localizable;
use App\Traits\Mediable;
use App\Traits\Priceable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory, Localizable, Mediable, Priceable, Discountable, Addonable;
    protected $guarded = ['id'];

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }


}
