<?php

namespace App\Repository\Eloquent;


use App\Models\Holiday;
use App\Repository\HolidayRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class HolidayRepository extends BaseRepository implements HolidayRepositoryInterface
{
    public static array $modelRelations = ['locales'];

    public function __construct(Holiday $model, private LocaleRepository $localeAction)
    {
        parent::__construct($model);
    }

    public function process($businessId, array $data)
    {
        return array_only($data, ['from', 'to', 'business_id', 'user_id']);
    }

    public function listModel($businessId)
    {
        return $this->model::with(['locales'])
            ->where('business_id', $businessId)
            ->orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function filter($businessId)
    {
        $request = request();
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);
        $startDate = $request->input('from');
        $endDate = $request->input('to');

        return $this->model::with(['locales'])
            ->where('business_id', $businessId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('from', [$startDate, $endDate])
                    ->orWhereBetween('to', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('from', '<=', $startDate)
                            ->where('to', '>=', $endDate);
                    });
            })
            ->orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function relations($model, $data)
    {
        if (isset($data['locales'])) {
            if (!$this->validateLocalesRelated($model, $data))
                abort(400,'Invalid Locales Data');
            $this->localeAction->setLocales($model, $data['locales']);
        }
    }

    public function validateHoliday($data)
    {
        if(!isset($data['from']) || !isset($data['to']))
            abort(400,'Invalid Date Format' );

        $datesConflict = Holiday::where('business_id', $data['business_id'])
            ->where(function ($query) use ($data) {
                $query->whereBetween('from', [$data['from'], $data['to']])
                    ->orWhereBetween('to', [$data['from'], $data['to']]);
            })
            ->first();
        if($datesConflict)
            abort(400,'Holiday already exists');
    }

    public function createModel($businessId, array $data): Model
    {
        $this->validateHoliday($data);

        $data['user_id'] = $data['user_id'] ?? auth('sanctum')->user()->id;
        $entity = $this->model->create($this->process($businessId, $data));
        $this->relations($entity, $data);
        return $this->model->with(HolidayRepository::$modelRelations)->find($entity->id);
    }

    public function updateModel($businessId, $id, array $data): Model
    {
        $model = tap($this->model->find($id))
            ->update($this->process($businessId, $data));
        $this->relations($model, $data);
        return $this->model->with(HolidayRepository::$modelRelations)->find($model->id);
    }

    public function get($businessId, int $id)
    {
        return $this->model->with(HolidayRepository::$modelRelations)->find($id);
    }

    public function destroy($businessId, $id): ?bool
    {
        $this->model->where(['business_id' => $businessId])->find($id)->locales->map(fn($locale) => $locale->delete());
        return $this->delete($id);
    }

}
