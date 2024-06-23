<?php


namespace App\Repository;


use Illuminate\Database\Eloquent\Model;

interface SettingRepositoryInterface
{
    public function createSetting($relationModel, $data);
    public function updateSetting($relationModel, $data): Model;
    public function listSettings($relationModel): mixed;
    public function getWorkingDays($restaurant_id);
    public function setSettings($relationModel, array $data);
}
