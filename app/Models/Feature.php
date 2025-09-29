<?php

namespace App\Models;

use App\Models\Items\SalonProduct;
use App\Models\Items\SalonService;
use App\Traits\Localizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasFactory, Localizable;

    protected $guarded = ['id'];
    protected $casts = ['sort' => 'integer', 'value' => 'json'];
    public $timestamps = false;

    public function salonServices()
    {
        return $this->morphedByMany(SalonService::class, 'featureable');
    }

    public function salonProducts()
    {
        return $this->morphedByMany(SalonProduct::class, 'featureable');
    }

}
