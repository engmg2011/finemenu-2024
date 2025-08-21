<?php


namespace App\Traits;


use App\Models\Media;

trait Mediable
{
    public function media() {
        return $this->morphMany(Media::class, 'mediable')
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'desc');
    }

    public function featuredImage()
    {
        return $this->morphOne(Media::class, 'mediable')
            ->where('type','like', '%image%')
            ->orderBy('id'); // or created_at, or any logic
    }

}
