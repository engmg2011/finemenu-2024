<?php

namespace App\Repository\Eloquent;


use App\Models\Items\SalonService;
use App\Models\User;
use App\Repository\SalonServiceRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class SalonServiceRepository extends BaseRepository implements SalonServiceRepositoryInterface
{

    public function __construct(SalonService $model)
    {
        parent::__construct($model);
    }

    public function process(array $data)
    {
        if(isset($data['provider_employee_ids']) && is_array($data['provider_employee_ids'])){
            $userBusinessIds = User::whereIn('id', $data['provider_employee_ids'])->pluck('business_id')->toArray();
            $businessId = request()->route('businessId');
            if(count($userBusinessIds) > 1 || (isset($userBusinessIds[0]) && $userBusinessIds[0] !== $businessId) ){
                abort(400, "Wrong Data");
            }
        }
        return array_only($data, ['item_id','duration',"provider_employee_ids"]);
    }

    public function createModel(array $data): Model
    {
        $entity = $this->model->create($this->process($data));
        return $entity;
    }

    public function updateModel($id, array $data): Model
    {
        $model = $this->model->find($id);
        $model->update($this->process($data));
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
