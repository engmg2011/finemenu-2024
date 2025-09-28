<?php

namespace App\Repository\Eloquent;


use App\Models\Area;
use App\Repository\AreaRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AreaRepository extends BaseRepository implements AreaRepositoryInterface
{

    public function __construct(Area $model, private LocaleRepository $localeAction)
    {
        parent::__construct($model);
    }

    public static array $modelRelations = ['locales', 'seats.locales'];


    private function area($businessId, $branchId): Builder
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
                abort(400,'Invalid Locales Data');
            $this->localeAction->setLocales($model, $data['locales']);
        }
    }

    public function createModel($businessId, $branchId, array $data): Model
    {
        $entity = $this->model->create($this->process($businessId, $branchId, $data));
        $this->relations($entity, $data);
        return $this->model->with(AreaRepository::$modelRelations)->find($entity->id);
    }

    public function updateModel($businessId, $branchId, $id, array $data): Model
    {
        $model = $this->area($businessId, $branchId)->find($id);
        if(!$model)
            abort(400,"Error: no area exists with the same id");
        $model->update($this->process($businessId, $branchId, $data));
        $this->relations($model, $data);
        return $this->model->with(AreaRepository::$modelRelations)->find($model->id);
    }

    public function sort($businessId, $branchId, $data)
    {
        $sort = 1;
        foreach ($data['sortedIds'] as $id) {
            $this->area($businessId, $branchId)->whereId($id)->update(['sort' => $sort]);
            $sort++;
        }
        return true;
    }

    public function get($businessId, $branchId, int $id)
    {
        return $this->area($businessId, $branchId)->with(AreaRepository::$modelRelations)->find($id);
    }

    public function destroy($businessId, $branchId, $id): ?bool
    {
        $this->area($businessId, $branchId)->find($id)?->locales->map(
            fn($locale) => $locale->delete()
        );
        return $this->area($businessId, $branchId)->find($id)?->delete();
    }

    public function branchAreas($business_id, $branch_id)
    {
        $withSeats = [];
        if (request()->has('full') && request('full') == 'true')
            $withSeats = ['seats.locales'];
        return $this->listWhere(
            ['business_id' => $business_id, 'branch_id' => $branch_id],
            ['locales' , ...$withSeats]
        );
    }

}
