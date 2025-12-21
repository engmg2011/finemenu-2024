<?php

namespace App\Repository\Eloquent\Itemable;


use App\Models\Items\CarProduct;
use App\Repository\Eloquent\BaseRepository;
use App\Repository\FeatureRepositoryInterface;
use App\Repository\ItemableInterfaces\CarProductRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class CarProductRepository extends BaseRepository implements CarProductRepositoryInterface
{

    public function __construct(CarProduct $model, private readonly FeatureRepositoryInterface $featureRepository)
    {
        parent::__construct($model);
    }

    public function process(array $data)
    {
        return array_only($data, ['item_id', 'color', 'brand_id', 'brand_id', 'model_id', 'model_id', 'year', 'vin',
            'license_plate', 'shape_type', 'mileage', 'engine_type', 'drive_type',]);
    }

    public function relations($model, array $data)
    {
        if (isset($data['featuresData']))
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
