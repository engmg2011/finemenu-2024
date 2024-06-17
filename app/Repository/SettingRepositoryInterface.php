<?php


namespace App\Repository;


use Illuminate\Database\Eloquent\Model;

interface SettingRepositoryInterface
{
    public function createSetting($relationModel, $data): Model;
    public function updateSetting($relationModel, $data): Model;
    public function listSettings($model): mixed;
    public function getWorkingDays($restaurant_id);
    public function setSettings($relationModel, array $data);
}
