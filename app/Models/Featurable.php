<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Featurable extends MorphPivot
{

    protected $casts = [
        'value' => 'array', // Laravel will cast JSON <-> array
    ];

}
