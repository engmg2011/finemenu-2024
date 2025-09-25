<?php

namespace App\Models\Items;

use App\Traits\Itemable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalonService extends Model
{

    use HasFactory, Itemable;

    protected $guarded = ['id'];
    protected $casts = ['duration' => 'integer', 'provider_employee_ids' => 'array'];
    protected $hidden = ['created_at', 'updated_at'];
}
