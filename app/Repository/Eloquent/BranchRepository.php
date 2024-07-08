<?php

namespace App\Repository\Eloquent;


use App\Models\Branch;
use App\Repository\BranchRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class BranchRepository extends BaseRepository implements BranchRepositoryInterface
{

    public function __construct(Branch $model, private LocaleRepository $localeAction)
    {
        parent::__construct($model);
    }

    public static array $modelRelations = ['locales'];


    public function process(array $data)
    {
        return array_only($data, ['restaurant_id', 'menu_id', 'sort', 'slug']);
    }

    public function relations($model, $data)
    {
        if (isset($data['locales'])) {
            if (!$this->validateLocalesRelated($model, $data))
                throw new \Exception('Invalid Locales Data');
            $this->localeAction->setLocales($model, $data['locales']);
        }
    }

    public function createModel($restaurantId, array $data): Model
    {
        $data['restaurant_id'] = $restaurantId;
        $entity = $this->model->create($this->process($data));
        $this->relations($entity, $data);
        return $this->model->with(BranchRepository::$modelRelations)->find($entity->id);
    }

    public function updateModel($restaurantId, $id, array $data): Model
    {
        $data['restaurant_id'] = $restaurantId;
        $model = $this->model->find($id);
        $this->relations($model, $data);
        $model->update($this->process($data));
        return $this->model->with(BranchRepository::$modelRelations)->find($model->id);
    }

    public function sort($restaurantId, $data)
    {
        $sort = 1;
        foreach ($data['sortedIds'] as $id) {
            $this->model->whereId($id)->update(['sort' => $sort]);
            $sort++;
        }
        return true;
    }


    public function get($restaurantId, int $id)
    {
        return $this->model->where(['restaurant_id' => $restaurantId])->with(BranchRepository::$modelRelations)->find($id);
    }

    public function destroy($restaurantId, $id): ?bool
    {
        $this->model->where(['restaurant_id' => $restaurantId])->find($id)?->locales->map(
            fn($locale) => $locale->delete()
        );
        return $this->model->where([
            'restaurant_id' => $restaurantId,
            'id' => $id
        ])?->delete();
    }

}
