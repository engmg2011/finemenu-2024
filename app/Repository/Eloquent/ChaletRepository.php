<?php

namespace App\Repository\Eloquent;


use App\Models\Items\Chalet;
use App\Repository\ChaletRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class ChaletRepository extends BaseRepository implements ChaletRepositoryInterface
{

    public function __construct(Chalet $model)
    {
        parent::__construct($model);
    }

    public function process(array $data)
    {
        return array_only($data, [
            'insurance', 'latitude', 'longitude', 'address', 'units',
            'frontage', 'bedrooms', 'item_id', 'owner_id', 'unit_names']);
    }

    public function createModel(array $data): Model
    {
        $entity = $this->model->create($this->process($data));
        return $entity;
    }

    public function updateModel($id, array $data): Model
    {
        $model = $this->model->find($id);

        $itemabelData =  $this->process($data);
        \Log::debug("will update". json_encode($itemabelData));

        $model->update($this->process($data));
        return $this->model->find($model->id);
    }

    public function set(array $data): Model
    {
        \Log::debug("should update". json_encode($data));

        if (isset($data['id']))
            return $this->updateModel($data['id'], $data);
        else
            return $this->create($data);
    }


}
