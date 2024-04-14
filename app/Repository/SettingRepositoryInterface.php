<?php


namespace App\Repository;


use Illuminate\Database\Eloquent\Model;

interface SettingRepositoryInterface
{
    public function createSetting($relationModel, $data): Model;
    public function updateSetting($relationModel, $data): Model;
    public function listSettings($model): mixed;


}
