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
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes): Model {
        return $this->model->create($attributes);
    }

    /**
     * @param array $attributes
     * @return Boolean
     */
    public function insert(array $attributes){
        return $this->model->insert($attributes);
    }

    /**
     * @param array $attributes
     * @return bool
     */
    public function update($id, array $attributes): bool
    {
        return $this->model->find($id)->update($id, $attributes);
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
}
