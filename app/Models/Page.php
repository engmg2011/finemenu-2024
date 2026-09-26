<?php

namespace App\Models;

use App\Traits\Localizable;
use App\Traits\Mediable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory, Localizable, Mediable;
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
