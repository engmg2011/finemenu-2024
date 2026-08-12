<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Models\LandingPageWidget;
use App\Repository\LandingPageWidgetRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LandingPageWidgetsController extends Controller
{
    public function __construct(private LandingPageWidgetRepositoryInterface $repository)
    {
    }

    public function index($businessId, $landingPageId)
    {
        return DataResource::collection($this->repository->listModel($businessId, $landingPageId));
    }

    public function create(Request $request, $businessId, $landingPageId): JsonResponse
    {
        $request->validate([
            'key' => 'required|string|unique:landing_page_widgets,key,NULL,id,landing_page_id,' . $landingPageId,
            'type' => 'required|string',
            'active' => 'nullable|boolean',
            'sort' => 'nullable|integer',
            'fields' => 'nullable|array',
            'data' => 'nullable|array',
            'locales' => 'nullable|array',
        ]);

        return response()->json($this->repository->createModel($businessId, $landingPageId, $request->all()));
    }

    public function show($businessId, $landingPageId, $id): JsonResponse
    {
        return response()->json($this->repository->get($businessId, $landingPageId, $id));
    }

    public function update(Request $request, $businessId, $landingPageId, $id): JsonResponse
    {
        $widget = LandingPageWidget::where('landing_page_id', $landingPageId)->findOrFail($id);
        $uniqueKeyRule = $request->key !== $widget->key
            ? '|unique:landing_page_widgets,key,NULL,id,landing_page_id,' . $landingPageId
            : '';

        $request->validate([
            'key' => 'required|string' . $uniqueKeyRule,
            'type' => 'required|string',
            'active' => 'nullable|boolean',
            'sort' => 'nullable|integer',
            'fields' => 'nullable|array',
            'data' => 'nullable|array',
            'locales' => 'nullable|array',
        ]);

        return response()->json($this->repository->updateModel($businessId, $landingPageId, $id, $request->all()));
    }

    public function destroy($businessId, $landingPageId, $id): JsonResponse
    {
        return response()->json($this->repository->destroy($businessId, $landingPageId, $id));
    }

    public function sort(Request $request, $businessId, $landingPageId): JsonResponse
    {
        $request->validate(['sortedIds' => 'required|array']);

        return response()->json($this->repository->sort($businessId, $landingPageId, $request->all()));
    }
}
