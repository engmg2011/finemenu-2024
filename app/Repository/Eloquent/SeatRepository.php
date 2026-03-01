<?php

namespace App\Repository\Eloquent;


use App\Models\Seat;
use App\Repository\SeatRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SeatRepository extends BaseRepository implements SeatRepositoryInterface
{

    public function __construct(Seat $model, private LocaleRepository $localeAction)
    {
        parent::__construct($model);
    }

    private function seat($areaId): Builder
    {
        return $this->model->where([
            'area_id' => $areaId
        ]);
    }

    public function list()
    {
        return $this->model::with(['locales'])
            ->orderByDesc('id')->paginate(request('per-page', 15));
    }


    public static array $modelRelations = ['locales'];


    public function process($areaId, array $data)
    {
        $data['area_id'] = $areaId;
        return array_only($data, ['area_id', 'sort']);
    }

    public function relations($model, $data)
    {
        // TODO :: Check all locales related to the same model

        if (isset($data['locales'])) {
            if (!$this->validateLocalesRelated($model, $data))
                abort(400,'Invalid Locales Data');

            $this->localeAction->setLocales($model, $data['locales']);
        }
    }

    public function createModel($areaId, array $data): Model
    {
        $entity = $this->model->create($this->process($areaId, $data));
        $this->relations($entity, $data);
        return $this->model->with(SeatRepository::$modelRelations)->find($entity->id);
    }

    public function updateModel($areaId, $id, array $data): Model
    {
        $model = $this->seat($areaId)->find($id);
        if (!$model)
            abort(400,'Error: no seat exists with the same id');
        $model->update($this->process($areaId, $data));
        $this->relations($model, $data);
        return $this->model->with(SeatRepository::$modelRelations)->find($model->id);
    }

    public function sort($areaId, $data)
    {
        $sort = 1;
        foreach ($data['sortedIds'] as $id) {
            $this->model->whereId($id)->update(['sort' => $sort]);
            $sort++;
        }
        return true;
    }


    public function get($areaId, int $id)
    {
        return $this->seat($areaId)->with(SeatRepository::$modelRelations)->find($id);
    }

    public function destroy($areaId, $id): ?bool
    {
        $this->seat($areaId)->find($id)?->locales->map(
            fn($locale) => $locale->delete()
        );
        return $this->seat($areaId)->find($id)?->delete();
    }

    public function areaSeats($seatId)
    {
        return $this->listWhere(
            ['area_id' => $seatId],
            ['locales']
        );
    }

    public function backup($businessId)
    {
        $seats = Seat::where('business_id', $businessId)->get();
        return compact('seats');
    }
}
