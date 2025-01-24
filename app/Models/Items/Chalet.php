<?php

namespace App\Models\Items;

use App\Traits\Itemable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chalet extends Model
{
    use HasFactory, Itemable;
    protected $guarded=['id'];
    protected $casts = ['address' => 'json'];

}
