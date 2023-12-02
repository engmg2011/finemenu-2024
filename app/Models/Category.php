<?php

namespace App\Models;

use App\Traits\Discountable;
use App\Traits\Localizable;
use App\Traits\Mediable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, Localizable, Mediable, Discountable;
    protected $guarded = ['id'];

    public function items() {
        return $this->hasMany(Item::class)->orderBy('sort');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function restaurant() {
        return $this->belongsTo(Restaurant::class);
    }

    public function children() {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort');
    }
}
