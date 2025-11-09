<?php

namespace App\Models;

use App\Traits\Localizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureOptions extends Model
{
    use HasFactory, Localizable;

    protected $guarded = ['id'];
    protected $hidden = ['feature_id'];
    public $timestamps = false;

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }
}
