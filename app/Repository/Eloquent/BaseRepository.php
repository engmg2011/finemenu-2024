<?php


namespace App\Repository\Eloquent;


use App\Repository\EloquentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\Types\Boolean;

class BaseRepository implements EloquentRepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    public function __construct(Model $model) {
        $this->model = $model;
    }

    /**
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model {
        return $this->model->create($data);
    }

    /**
     * @param array $data
     * @return Boolean
     */
    public function insert(array $data){
        return $this->model->insert($data);
    }

    /**
     * @param array $data
     * @return bool | Model
     */
    public function update($id, array $data): bool | Model
    {
        return $this->model->find($id)->update($id, $data);
    }

    /**
     * @param $id
     * @return Model|null
     */
    public function find($id): ?Model {
        return $this->model->find($id);
    }

    /**
     * @return Collection
     */
    public function all(): Collection {
        return $this->model->all();
    }

    public function createMany($data)
    {
        return $this->model->insert($data);
    }

    public function firstOrCreate(array $attributes, array $values = [])
    {
        return $this->model->firstOrCreate($attributes,$values);
    }

    public function updateOrCreate(array $attributes, array $values = [])
    {
        return $this->model->updateOrCreate($attributes,$values);
    }

    public function delete($id)
    {
        return $this->model->find($id)->delete();
    }

    public function where($data)
    {
        return $this->model->where($data);
    }
    public function with($data)
    {
        return $this->model->with($data);
    }
    public function whereId($id)
    {
        return $this->model->whereId($id);
    }

    public function list()
    {
        return $this->model::orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function validateLocalesRelated(&$model, &$data)
    {
        $validData = true;
        $localeIds = [];
        foreach ($model->locales as &$locale)
            $localeIds[] = $locale['id'];
        foreach ($data['locales'] as $dataLocale){
            if(isset($dataLocale['id']) && !in_array($dataLocale['id'] , $localeIds)){
                $validData = false;
                break;
            }
        }
        return $validData;
    }
}
