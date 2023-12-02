<?php

namespace App\Models;

use App\Traits\Localizable;
use App\Traits\Mediable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory, Localizable, Mediable;
    protected $guarded = ['id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

}
