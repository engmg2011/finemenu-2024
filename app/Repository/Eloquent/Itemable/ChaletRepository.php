<?php

namespace App\Repository\Eloquent\Itemable;


use App\Models\Items\Chalet;
use App\Repository\Eloquent\BaseRepository;
use App\Repository\FeatureRepositoryInterface;
use App\Repository\ItemableInterfaces\ChaletRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class ChaletRepository extends BaseRepository implements ChaletRepositoryInterface
{

    public function __construct(Chalet $model,
                                private readonly FeatureRepositoryInterface $featureRepository)
    {
        parent::__construct($model);
    }

    public function process(array $data)
    {
        $data = array_filter($data, fn($value) => $value !== null);

        if(isset($data['units']) &&  ((int) $data['units'])< 1)
            $data['units'] = 1;

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
