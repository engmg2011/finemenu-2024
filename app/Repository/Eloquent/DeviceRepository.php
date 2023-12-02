<?php

namespace App\Repository\Eloquent;


use App\Models\Device;
use App\Repository\DeviceRepositoryInterface;

class DeviceRepository extends BaseRepository implements DeviceRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param Device $model
     */
    public function __construct(Device $model) {
        parent::__construct($model);
    }

}
