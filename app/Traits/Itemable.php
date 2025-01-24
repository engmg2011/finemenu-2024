<?php


namespace App\Traits;


use App\Models\Item;

trait Itemable
{
    public function item() {
        return $this->morphOne(Item::class, 'itemable');
    }


}
