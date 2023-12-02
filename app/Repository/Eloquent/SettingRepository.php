<?php

namespace App\Repository\Eloquent;



use App\Models\Setting;
use App\Repository\SettingRepositoryInterface;

class SettingRepository extends BaseRepository implements SettingRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param Setting $model
     */
    public function __construct(Setting $model) {
        parent::__construct($model);
    }

}
