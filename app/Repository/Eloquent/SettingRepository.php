<?php

namespace App\Repository\Eloquent;

//
use App\Actions\DiscountAction;
use App\Actions\MediaAction;
use App\Constants\SettingConstants;
use App\Models\Business;
use App\Models\Setting;
use App\Repository\SettingRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class SettingRepository extends BaseRepository implements SettingRepositoryInterface
{

    /*
     * Shifts like
        [{"day": "Sat","shift": [{"00:00": "01:00"}]},
         {"day": "Sun","shift": [{"00:00": "01:00"}]} ]
    */

    public array $relations = ['locales', 'media', 'discounts'];

    /**
     * UserRepository constructor.
     * @param Setting $model
     */
    public function __construct(Setting                           $model,
                                private readonly MediaAction      $mediaAction,
                                private readonly LocaleRepository $localeAction,
                                private readonly DiscountAction   $discountAction
    )
    {
        parent::__construct($model);
    }


    public function process(array $data): array
    {
        $data['user_id'] = auth('api')->user()->id;
        return array_only($data, ['settable_id', 'settable_type', 'key', 'data', 'user_id']);
    }

    public function setSettable(&$relationModel, &$data)
    {
        $data['settable_id'] = $relationModel->id;
        $data['settable_type'] = get_class($relationModel);
        $data['user_id'] = auth('api')->user()->id;
    }

    public function relationsProcess(&$model, &$data): void
    {
        if (isset($data['locales']))
            $this->localeAction->setLocales($model, $data['locales']);
        if (isset($data['media']))
            $this->mediaAction->setMedia($model, $data['media']);
        if (isset($data['discounts']))
            $this->discountAction->set($model, $data['discounts']);
    }

    /**
     * @param $relationModel : Like business which will be set
     * @param $data
     * @return Model
     */
    public function createSetting($relationModel, $data)
    {
        $this->setSettable($relationModel, $data);
        $this->relationsProcess($relationModel, $data);
        return $this->model->create($this->process($data));
    }

    /**
     * @param $relationModel : Like business which will be set
     * @param $data
     * @return Model
     */
    public function updateSetting($relationModel, $data): Model
    {
        $this->setSettable($relationModel, $data);
        $this->relationsProcess($relationModel, $data);
        return tap($this->model->find($data['id']))
            ->update($this->process($data));
    }

    /**
     * @param $relationModel : Like business which will be set
     * @param $deleteData
     */
    public function deleteSetting($relationModel, $data)
    {
        $deleteData = [];
        $this->setSettable($relationModel, $deleteData);
        unset($deleteData['user_id']);
        $setting = $this->model->where($deleteData)->find($data['id']);
        if ($setting)
            return $setting->delete();
        throw new \Exception("No data found!");
    }

    public function set($model, $data)
    {
        return isset($data['id']) ? $this->update($model, $data) : $this->create($model, $data);
    }

    public function listSettings($relationModel): mixed
    {
        $data = [];
        $data['settable_id'] = $relationModel->id;
        $data['settable_type'] = get_class($relationModel);
        return $this->model::where($data)->get();
    }

    private function getShifts($business_id)
    {
        $business = Business::find($business_id);
        return $business->settings
            ->where('key', SettingConstants::Keys['SHIFTS'])
            ->first()?->data;
    }

    public function getWorkingDays($business_id)
    {
        $shifts = $this->getShifts($business_id);
        if (is_null($shifts))
            return SettingConstants::WORK_DAYS;
        return array_keys($shifts);
    }

    public function setSettings($relationModel, array $data)
    {
        $settings = $data['settings'];
        foreach ($settings as  $setting) {
            $keySetting = $relationModel->settings?->where('key', $setting['key'])->first();
            if ($keySetting)
                $keySetting->update(['data' => $setting['data']]);
            else
                $this->createSetting($relationModel, $setting);
        }
        return $this->listSettings($relationModel);
    }
}
