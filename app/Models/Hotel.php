<?php

namespace App\Models;

use App\Traits\Contactable;
use App\Traits\Discountable;
use App\Traits\Localizable;
use App\Traits\Mediable;
use App\Traits\Orderable;
use App\Traits\Settable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasFactory, Settable, Contactable, Orderable, Mediable, Localizable, Discountable;
    protected $guarded = ['id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

}
