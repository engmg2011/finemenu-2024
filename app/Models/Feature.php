<?php

namespace App\Models;

use App\Models\Items\SalonProduct;
use App\Models\Items\SalonService;
use App\Traits\Categorizable;
use App\Traits\Localizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasFactory, Localizable, Categorizable;

    protected $guarded = ['id'];
    protected $casts = ['sort' => 'integer', 'value' => 'json' , 'options' => 'array'];
    public $timestamps = false;

    public function salonServices()
    {
        return $this->morphedByMany(SalonService::class, 'featureable');
    }

    public function salonProducts()
    {
        return $this->morphedByMany(SalonProduct::class, 'featureable');
    }

    public function feature_options()
    {
        return $this->hasMany(FeatureOptions::class, 'feature_id');
    }

}
