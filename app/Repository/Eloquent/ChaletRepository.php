<?php

namespace App\Repository\Eloquent;


use App\Models\Items\Chalet;
use App\Repository\ChaletRepositoryInterface;
use App\Repository\FeatureRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class ChaletRepository extends BaseRepository implements ChaletRepositoryInterface
{

    public function __construct(Chalet $model, private readonly FeatureRepositoryInterface $featureRepository)
    {
        parent::__construct($model);
    }

    public function process(array $data)
    {
        return array_only($data, [
            'insurance', 'latitude', 'longitude', 'address', 'units',
            'frontage', 'bedrooms', 'item_id', 'owner_id', 'unit_names']);
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
