<?php

namespace App\Repository\Eloquent;


use App\Models\Items\SalonService;
use App\Models\User;
use App\Repository\FeatureRepositoryInterface;
use App\Repository\SalonServiceRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class SalonServiceRepository extends BaseRepository implements SalonServiceRepositoryInterface
{

    public function __construct(SalonService $model, private readonly FeatureRepositoryInterface $featureRepository)
    {
        parent::__construct($model);
    }

    public function process(array $data)
    {
        if(isset($data['provider_employee_ids']) && is_array($data['provider_employee_ids'])){
            $userBusinessIds = User::whereIn('id', $data['provider_employee_ids'])
                ->groupBy('business_id')
                ->pluck('business_id')->toArray();
            $businessId = (int) request()->route('businessId');

            if(count($userBusinessIds) > 1 || (isset($userBusinessIds[0]) && $userBusinessIds[0] !== $businessId) ){
                abort(400, "Wrong Data");
            }
        }
        return array_only($data, ['item_id','duration',"provider_employee_ids"]);
    }

    public function relations($model , array $data)
    {
        if(isset($data['featuresData']))
            $this->featureRepository->setFeatures($model, $data['featuresData']);

    }

    public function createModel(array $data): Model
    {
        $model = $this->model->create($this->process($data));
        $this->relations($model, $data);
        return $model;
    }

    public function updateModel($id, array $data): Model
    {
        $model = $this->model->find($id);
        $model->update($this->process($data));
        $this->relations($model, $data);
        return $this->model->find($model->id);
    }

    public function set(array $data): Model
    {
        if (isset($data['id']))
            return $this->updateModel($data['id'], $data);
        else
            return $this->create($data);
    }


}
