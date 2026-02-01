<?php

namespace App\Models\Items\Influencer;

use App\Traits\Featurable;
use App\Traits\Itemable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfluencerService extends Model
{
    use HasFactory, Itemable, Featurable;

    protected $guarded = ['id'];
    protected $casts = ['duration' => 'integer', 'provider_employee_ids' => 'array'];
    protected $hidden = ['created_at', 'updated_at'];
    public $timestamps = false;
}
