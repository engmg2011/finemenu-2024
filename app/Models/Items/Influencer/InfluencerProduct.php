<?php

namespace App\Models\Items\Influencer;

use App\Traits\Featurable;
use App\Traits\Itemable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfluencerProduct extends Model
{
    use HasFactory, Itemable, Featurable;
    protected $guarded=['id'];
    protected $casts = ['amount'=>'integer'];
    public $timestamps = false;
}
