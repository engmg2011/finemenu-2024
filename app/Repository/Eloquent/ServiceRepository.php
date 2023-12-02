<?php

namespace App\Repository\Eloquent;



use App\Models\Service;
use App\Repository\ServiceRepositoryInterface;

class ServiceRepository extends BaseRepository implements ServiceRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param Service $model
     */
    public function __construct(Service $model) {
        parent::__construct($model);
    }

}
