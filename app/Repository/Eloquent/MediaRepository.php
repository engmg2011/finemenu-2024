<?php

namespace App\Repository\Eloquent;


use App\Models\Media;
use App\Repository\MediaRepositoryInterface;

class MediaRepository extends BaseRepository implements MediaRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param Media $model
     */
    public function __construct(Media $model) {
        parent::__construct($model);
    }

}
