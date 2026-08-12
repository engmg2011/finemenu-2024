<?php

namespace App\Repository\Eloquent;

use App\Models\LandingPage;
use App\Models\LandingPageWidget;
use App\Repository\LandingPageWidgetRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class LandingPageWidgetRepository extends BaseRepository implements LandingPageWidgetRepositoryInterface
{
    public static array $modelRelations = ['locales'];

    public function __construct(LandingPageWidget $model, private LocaleRepository $localeRepository)
    {
        parent::__construct($model);
    }

    public function process($landingPageId, array $data): array
    {
        $data['landing_page_id'] = $landingPageId;

        return array_only($data, ['landing_page_id', 'key', 'type', 'active', 'sort', 'fields', 'data']);
    }

    public function relations($model, array $data): void
    {
        if (isset($data['locales'])) {
            if (!$this->validateLocalesRelated($model, $data))
                abort(400, 'Invalid Locales Data');
            $this->localeRepository->setLocales($model, $data['locales']);
        }
    }

    public function landingPage($businessId, $landingPageId): LandingPage
    {
        $landingPage = LandingPage::where('business_id', $businessId)->find($landingPageId);
        if (!$landingPage)
            abort(400, 'Error: no landing page exists with the same id');

        return $landingPage;
    }

    public function listModel($businessId, $landingPageId)
    {
        $this->landingPage($businessId, $landingPageId);

        return $this->model
            ->where('landing_page_id', $landingPageId)
            ->with(self::$modelRelations)
            ->orderBy('sort')
            ->get();
    }

    public function createModel($businessId, $landingPageId, array $data): Model
    {
        $this->landingPage($businessId, $landingPageId);
        $data['sort'] = $data['sort'] ?? (($this->model->where('landing_page_id', $landingPageId)->max('sort') ?? 0) + 1);
        $entity = $this->model->create($this->process($landingPageId, $data));
        $this->relations($entity, $data);

        return $this->model->with(self::$modelRelations)->find($entity->id);
    }

    public function updateModel($businessId, $landingPageId, $id, array $data): Model
    {
        $this->landingPage($businessId, $landingPageId);
        $model = $this->model->where('landing_page_id', $landingPageId)->find($id);
        if (!$model)
            abort(400, 'Error: no data exist with the same id');

        $model->update($this->process($landingPageId, $data));
        $this->relations($model, $data);

        return $this->model->with(self::$modelRelations)->find($model->id);
    }

    public function get($businessId, $landingPageId, int $id)
    {
        $this->landingPage($businessId, $landingPageId);

        return $this->model
            ->where('landing_page_id', $landingPageId)
            ->with(self::$modelRelations)
            ->find($id);
    }

    public function destroy($businessId, $landingPageId, $id): ?bool
    {
        $this->landingPage($businessId, $landingPageId);
        $model = $this->model->where('landing_page_id', $landingPageId)->find($id);
        if (!$model)
            abort(400, 'Error: no data exist with the same id');

        $model->locales->map(fn($locale) => $locale->delete());

        return $model->delete();
    }

    public function sort($businessId, $landingPageId, array $data)
    {
        $this->landingPage($businessId, $landingPageId);
        $sort = 1;
        foreach ($data['sortedIds'] as $id) {
            $this->model->where('landing_page_id', $landingPageId)->whereId($id)->update(['sort' => $sort]);
            $sort++;
        }

        return true;
    }
}
