<?php


namespace App\Actions;

use App\Models\Setting;
use App\Repository\Eloquent\SettingRepository;
use Illuminate\Database\Eloquent\Model;

class SettingAction
{
    public function __construct(private SettingRepository $repository)
    {
    }

    public function process(array $data): array
    {
        return array_only($data, ['settable_id', 'settable_type', 'data', 'user_id']);
    }

    public function setSettable(&$model, &$data)
    {
        $data['settable_id'] = $model->id;
        $data['settable_type'] = get_class($model);
        $data['user_id'] = auth('api')->user()->id;
    }

    public function create($model, $data): Model
    {
        $this->setSettable($model, $data);
        return $this->repository->create($this->process($data));
    }

    public function update($model, $data): Model
    {
        $this->setSettable($model, $data);
        return tap($this->repository->find($data['id']))
            ->update($this->process($data));
    }

    public function set($model, $data)
    {
        return isset($data['id']) ? $this->update($model, $data) : $this->create($model, $data);
    }

    /**
     * @return mixed
     */
    public function list()
    {
        return Setting::orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function getModel(int $id)
    {
        return Setting::find($id);
    }

}
