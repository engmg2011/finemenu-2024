<?php

namespace App\Repository\Eloquent;


use App\Models\Addon;
use App\Repository\AddonRepositoryInterface;

class AddonRepository extends BaseRepository implements AddonRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param Addon $model
     */
    public function __construct(Addon $model)
    {
        parent::__construct($model);
    }

}
