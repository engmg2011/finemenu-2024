<?php

namespace App\Repository\Eloquent;

use App\Models\LandingPage;
use App\Models\LandingPageWidget;
use App\Models\LandingPageWidgetTemplate;
use App\Repository\LandingPageWidgetTemplateRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class LandingPageWidgetTemplateRepository extends BaseRepository implements LandingPageWidgetTemplateRepositoryInterface
{
    public static array $modelRelations = ['locales'];

    public function __construct(LandingPageWidgetTemplate $model, private LocaleRepository $localeRepository)
    {
        parent::__construct($model);
    }

    public function listTemplates(?string $businessType)
    {
        return $this->model
            ->when($businessType, fn($q) => $q->where('business_type', $businessType))
            ->with(self::$modelRelations)
            ->orderBy('sort')
            ->get();
    }

    public function get(int $id): Model
    {
        $template = $this->model->with(self::$modelRelations)->find($id);
        if (!$template) {
            abort(404, 'Template not found');
        }

        return $template;
    }

    public function cloneFromWidget(int $widgetId, string $name, int $userId): Model
    {
        $widget = LandingPageWidget::with(['landingPage.business', 'locales'])->findOrFail($widgetId);

        $businessType = $widget->landingPage?->business?->type;
        if (!$businessType) {
            abort(400, 'Could not determine business type from widget');
        }

        $template = $this->model->create([
            'name'          => $name,
            'business_type' => $businessType,
            'key'           => $widget->key,
            'type'          => $widget->type,
            'active'        => true,
            'sort'          => $widget->sort,
            'fields'        => $widget->fields,
            'data'          => $widget->data,
            'created_by'    => $userId,
        ]);

        if ($widget->locales->isNotEmpty()) {
            $localesData = $widget->locales->map(fn($l) => [
                'locale' => $l->locale,
                'data'   => $l->data,
            ])->toArray();
            $this->localeRepository->setLocales($template, $localesData);
        }

        return $this->model->with(self::$modelRelations)->find($template->id);
    }

    public function cloneToWidget(int $templateId, int $landingPageId): Model
    {
        $template = $this->get($templateId);

        $landingPage = LandingPage::findOrFail($landingPageId);

        // Generate a unique key within the landing page
        $baseKey = $template->key;
        $key     = $baseKey;
        $counter = 1;
        while (LandingPageWidget::where('landing_page_id', $landingPageId)->where('key', $key)->exists()) {
            $key = $baseKey . '_' . $counter;
            $counter++;
        }

        $maxSort = LandingPageWidget::where('landing_page_id', $landingPageId)->max('sort') ?? 0;

        $widget = LandingPageWidget::create([
            'landing_page_id' => $landingPageId,
            'key'             => $key,
            'type'            => $template->type,
            'active'          => $template->active,
            'sort'            => $maxSort + 1,
            'fields'          => $template->fields,
            'data'            => $template->data,
        ]);

        if ($template->locales->isNotEmpty()) {
            $localesData = $template->locales->map(fn($l) => [
                'locale' => $l->locale,
                'data'   => $l->data,
            ])->toArray();
            $this->localeRepository->setLocales($widget, $localesData);
        }

        return LandingPageWidget::with(['locales'])->find($widget->id);
    }

    public function destroy(int $id): ?bool
    {
        $template = $this->get($id);
        $template->locales->map(fn($locale) => $locale->delete());

        return $template->delete();
    }
}
