<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;

interface LandingPageWidgetRepositoryInterface
{
    public function listModel($businessId, $landingPageId);

    public function createModel($businessId, $landingPageId, array $data): Model;

    public function updateModel($businessId, $landingPageId, $id, array $data): Model;

    public function get($businessId, $landingPageId, int $id);

    public function destroy($businessId, $landingPageId, $id): ?bool;

    public function sort($businessId, $landingPageId, array $data);
}
