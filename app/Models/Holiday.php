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

    protected $hidden = ['pivot','created_at','updated_at']; // Hide pivot data from response

    protected $appends = ['price']; // Add a computed attribute

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class)->withPivot('price');
    }

    public function getPriceAttribute()
    {
        return ((float) $this->pivot?->price) ?? null; // Get price from pivot table
    }
}
