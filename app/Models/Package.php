<?php

namespace App\Models;

use App\Traits\Localizable;
use App\Traits\Orderable;
use App\Traits\Priceable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory, Localizable, Priceable, Orderable;

    protected $guarded = ['id'];
}
