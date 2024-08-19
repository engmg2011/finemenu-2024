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


    private function floor($businessId, $branchId): Builder
    {
        return $this->model->where([
            'business_id' => $businessId,
            'branch_id' => $branchId
        ]);
    }

    public function process($businessId, $branchId, array $data)
    {
        $data['branch_id'] = $branchId;
        $data['business_id'] = $businessId;
        return array_only($data, ['business_id', 'branch_id', 'sort']);
    }

    public function relations($model, $data)
    {
        if (isset($data['locales'])) {
            if (!$this->validateLocalesRelated($model, $data))
                throw new \Exception('Invalid Locales Data');
            $this->localeAction->setLocales($model, $data['locales']);
        }
    }

    public function createModel($businessId, $branchId, array $data): Model
    {
        $entity = $this->model->create($this->process($businessId, $branchId, $data));
        $this->relations($entity, $data);
        return $this->model->with(FloorRepository::$modelRelations)->find($entity->id);
    }

    public function updateModel($businessId, $branchId, $id, array $data): Model
    {
        $model = $this->floor($businessId, $branchId)->find($id);
        if(!$model)
            throw new \Exception("Error: no floor exists with the same id");
        $model->update($this->process($businessId, $branchId, $data));
        $this->relations($model, $data);
        return $this->model->with(FloorRepository::$modelRelations)->find($model->id);
    }

    public function sort($businessId, $branchId, $data)
    {
        $sort = 1;
        foreach ($data['sortedIds'] as $id) {
            $this->floor($businessId, $branchId)->whereId($id)->update(['sort' => $sort]);
            $sort++;
        }
        return true;
    }

    public function get($businessId, $branchId, int $id)
    {
        return $this->floor($businessId, $branchId)->with(FloorRepository::$modelRelations)->find($id);
    }

    public function destroy($businessId, $branchId, $id): ?bool
    {
        $this->floor($businessId, $branchId)->find($id)?->locales->map(
            fn($locale) => $locale->delete()
        );
        return $this->floor($businessId, $branchId)->find($id)?->delete();
    }

    public function branchFloors( $business_id, $branch_id)
    {
        return $this->listWhere(
            ['business_id' => $business_id, 'branch_id' => $branch_id],
            ['locales']
        );
    }

}
