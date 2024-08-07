<?php

namespace App\Models;

use App\Traits\Localizable;
use App\Traits\Mediable;
use App\Traits\Settable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Branch extends Model
{
    use HasFactory, Localizable, Settable, Mediable;

    protected $guarded = ['id'];
    public $timestamps = false;

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id');
    }

    public function floors()
    {
        return $this->hasMany(Floor::class, 'branch_id');
    }


}
