<?php

namespace App\Repository\Eloquent\Itemable\Influencer;


use App\Models\Items\Influencer\InfluencerProduct;
use App\Repository\Eloquent\BaseRepository;
use App\Repository\FeatureRepositoryInterface;
use App\Repository\ItemableInterfaces\Influencer\InfluencerProductRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class InfluencerProductRepository extends BaseRepository implements InfluencerProductRepositoryInterface
{

    public function __construct(InfluencerProduct $model, private readonly FeatureRepositoryInterface $featureRepository)
    {
        parent::__construct($model);
    }

    public function process(array $data)
    {
        return array_only($data, ['item_id','amount']);
    }

    public function relations($model , array $data)
    {
        if(isset($data['featuresData']))
            $this->featureRepository->setFeatures($model, $data['featuresData']);

    }

    public function createModel(array $data): Model
    {
        if(!isset($data['amount']))
            $data['amount'] = 1;
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
