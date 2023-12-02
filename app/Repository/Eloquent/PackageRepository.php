<?php

namespace App\Repository\Eloquent;



use App\Models\Package;
use App\Repository\PackageRepositoryInterface;

class PackageRepository extends BaseRepository implements PackageRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param Package $model
     */
    public function __construct(Package $model) {
        parent::__construct($model);
    }

}
