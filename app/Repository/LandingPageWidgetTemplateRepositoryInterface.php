<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;

interface LandingPageWidgetTemplateRepositoryInterface
{
    public function listTemplates(?string $businessType);

    public function get(int $id): Model;

    public function cloneFromWidget(int $widgetId, string $name, int $userId): Model;

    public function cloneToWidget(int $templateId, int $landingPageId): Model;

    public function destroy(int $id): ?bool;
}
