<?php

namespace App\Repository\Eloquent;


use App\Models\Floor;
use App\Repository\FloorRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FloorRepository extends BaseRepository implements FloorRepositoryInterface
{

    public function __construct(Floor $model, private LocaleRepository $localeAction)
    {
        parent::__construct($model);
    }

    public static array $modelRelations = ['locales', 'tables.locales'];


    private function floor($restaurantId, $branchId): Builder
    {
        return $this->model->where([
            'restaurant_id' => $restaurantId,
            'branch_id' => $branchId
        ]);
    }

    public function process($restaurantId, $branchId, array $data)
    {
        $data['branch_id'] = $branchId;
        $data['restaurant_id'] = $restaurantId;
        return array_only($data, ['restaurant_id', 'branch_id', 'sort']);
    }

    public function relations($model, $data)
    {
        if (isset($data['locales'])) {
            if (!$this->validateLocalesRelated($model, $data))
                throw new \Exception('Invalid Locales Data');
            $this->localeAction->setLocales($model, $data['locales']);
        }
    }

    public function createModel($restaurantId, $branchId, array $data): Model
    {
        $entity = $this->model->create($this->process($restaurantId, $branchId, $data));
        $this->relations($entity, $data);
        return $this->model->with(FloorRepository::$modelRelations)->find($entity->id);
    }

    public function updateModel($restaurantId, $branchId, $id, array $data): Model
    {
        $model = $this->floor($restaurantId, $branchId)->find($id);
        if(!$model)
            throw new \Exception("Error: no floor exists with the same id");
        $model->update($this->process($restaurantId, $branchId, $data));
        $this->relations($model, $data);
        return $this->model->with(FloorRepository::$modelRelations)->find($model->id);
    }

    public function sort($restaurantId, $branchId, $data)
    {
        $sort = 1;
        foreach ($data['sortedIds'] as $id) {
            $this->floor($restaurantId, $branchId)->whereId($id)->update(['sort' => $sort]);
            $sort++;
        }
        return true;
    }

    public function get($restaurantId, $branchId, int $id)
    {
        return $this->floor($restaurantId, $branchId)->with(FloorRepository::$modelRelations)->find($id);
    }

    public function destroy($restaurantId, $branchId, $id): ?bool
    {
        $this->floor($restaurantId, $branchId)->find($id)?->locales->map(
            fn($locale) => $locale->delete()
        );
        return $this->floor($restaurantId, $branchId)->find($id)?->delete();
    }

    public function branchFloors($restaurant_id, $branch_id)
    {
        return $this->listWhere(
            ['restaurant_id' => $restaurant_id, 'branch_id' => $branch_id],
            ['locales']
        );
    }

}
