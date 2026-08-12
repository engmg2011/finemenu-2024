<?php

namespace App\Models;

use App\Traits\Localizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LandingPage extends Model
{
    use HasFactory, Localizable;

    protected $guarded = ['id'];
    protected $casts = [
        'active' => 'boolean',
        'sort' => 'integer',
        'data' => 'json',
    ];
    protected $hidden = ['business_id', 'user_id'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function widgets(): HasMany
    {
        return $this->hasMany(LandingPageWidget::class)->orderBy('sort');
    }
}
