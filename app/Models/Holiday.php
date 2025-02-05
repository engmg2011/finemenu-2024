<?php

namespace App\Models;

use App\Traits\Localizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Holiday extends Model
{
    use HasFactory, Localizable;
    protected $guarded = ['id'];


    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class)->withPivot('price');
    }
}
