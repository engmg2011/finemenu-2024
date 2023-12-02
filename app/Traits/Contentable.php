<?php

namespace App\Traits;

use App\Models\Content;

trait Contentable
{

    public function contents() {
        return $this->morphMany(Content::class, 'contentable');
    }

}
