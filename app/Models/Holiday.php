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

    protected $hidden = ['pivot']; // Hide pivot data from response

    protected $appends = ['price']; // Add a computed attribute

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class)->withPivot('price');
    }

    public function getPriceAttribute()
    {
        return $this->pivot->price ?? null; // Get price from pivot table
    }
}
