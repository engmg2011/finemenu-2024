<?php

namespace App\Models;

use App\Traits\Localizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandingPageWidgetTemplate extends Model
{
    use HasFactory, Localizable;

    protected $guarded = ['id'];
    protected $casts = [
        'active' => 'boolean',
        'sort'   => 'integer',
        'fields' => 'json',
        'data'   => 'json',
    ];
    protected $hidden = ['created_by'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
