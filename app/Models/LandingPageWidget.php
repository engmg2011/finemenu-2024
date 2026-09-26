<?php

namespace App\Models;

use App\Traits\Localizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandingPageWidget extends Model
{
    use HasFactory, Localizable;

    protected $guarded = ['id'];
    protected $casts = [
        'active' => 'boolean',
        'sort' => 'integer',
        'fields' => 'json',
        'data' => 'json',
    ];
    protected $hidden = ['landing_page_id'];

    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class);
    }
}
