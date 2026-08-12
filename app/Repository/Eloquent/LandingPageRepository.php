<?php

namespace App\Repository\Eloquent;

use App\Models\LandingPage;
use App\Repository\LandingPageRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class LandingPageRepository extends BaseRepository implements LandingPageRepositoryInterface
{
    public static array $modelRelations = ['locales', 'widgets.locales'];

    public function __construct(LandingPage $model, private LocaleRepository $localeRepository)
    {
        parent::__construct($model);
    }

    public function process($businessId, array $data): array
    {
        $data['business_id'] = $businessId;
        $data['user_id'] = auth('sanctum')->user()?->id ?? $data['user_id'] ?? null;

        return array_only($data, ['business_id', 'key', 'slug', 'active', 'sort', 'data', 'user_id']);
    }

    public function relations($model, array $data): void
    {
        if (isset($data['locales'])) {
            if (!$this->validateLocalesRelated($model, $data))
                abort(400, 'Invalid Locales Data');
            $this->localeRepository->setLocales($model, $data['locales']);
        }
    }

    public function listModel($businessId)
    {
        return $this->model
            ->where('business_id', $businessId)
            ->with(self::$modelRelations)
            ->orderBy('sort')
            ->get();
    }

    public function createModel($businessId, array $data): Model
    {
        $data['sort'] = $data['sort'] ?? (($this->model->where('business_id', $businessId)->max('sort') ?? 0) + 1);
        $entity = $this->model->create($this->process($businessId, $data));
        $this->relations($entity, $data);

        return $this->model->with(self::$modelRelations)->find($entity->id);
    }

    public function updateModel($businessId, $id, array $data): Model
    {
        $model = $this->model->where('business_id', $businessId)->find($id);
        if (!$model)
            abort(400, 'Error: no data exist with the same id');

        $model->update($this->process($businessId, $data));
        $this->relations($model, $data);

        return $this->model->with(self::$modelRelations)->find($model->id);
    }

    public function get($businessId, int $id)
    {
        return $this->model
            ->where('business_id', $businessId)
            ->with(self::$modelRelations)
            ->find($id);
    }

    public function getByKey($businessId, string $key)
    {
        return $this->model
            ->where('business_id', $businessId)
            ->where('key', $key)
            ->with(self::$modelRelations)
            ->first();
    }

    public function destroy($businessId, $id): ?bool
    {
        $model = $this->model->where('business_id', $businessId)->find($id);
        if (!$model)
            abort(400, 'Error: no data exist with the same id');

        $model->locales->map(fn($locale) => $locale->delete());
        $model->widgets->map(function ($widget) {
            $widget->locales->map(fn($locale) => $locale->delete());
        });

        return $model->delete();
    }

    public function sort($businessId, array $data)
    {
        $sort = 1;
        foreach ($data['sortedIds'] as $id) {
            $this->model->where('business_id', $businessId)->whereId($id)->update(['sort' => $sort]);
            $sort++;
        }

        return true;
    }
}
