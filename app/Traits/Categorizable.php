<?php

namespace App\Traits;

use App\Models\Category;

trait Categorizable{

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

}
