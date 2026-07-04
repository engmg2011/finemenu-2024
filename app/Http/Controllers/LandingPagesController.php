<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Models\LandingPage;
use App\Repository\LandingPageRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LandingPagesController extends Controller
{
    public function __construct(private LandingPageRepositoryInterface $repository)
    {
    }

    public function index($businessId)
    {
        return DataResource::collection($this->repository->listModel($businessId));
    }

    public function create(Request $request, $businessId): JsonResponse
    {
        $request->validate([
            'key' => 'required|string|unique:landing_pages,key,NULL,id,business_id,' . $businessId,
            'slug' => 'nullable|string',
            'active' => 'nullable|boolean',
            'sort' => 'nullable|integer',
            'data' => 'nullable|array',
            'locales' => 'nullable|array',
        ]);

        return response()->json($this->repository->createModel($businessId, $request->all()));
    }

    public function show($businessId, $id): JsonResponse
    {
        return response()->json($this->repository->get($businessId, $id));
    }

    public function showByKey($businessId, $key): JsonResponse
    {
        return response()->json($this->repository->getByKey($businessId, $key));
    }

    public function update(Request $request, $businessId, $id): JsonResponse
    {
        $landingPage = LandingPage::where('business_id', $businessId)->findOrFail($id);
        $uniqueKeyRule = $request->key !== $landingPage->key
            ? '|unique:landing_pages,key,NULL,id,business_id,' . $businessId
            : '';

        $request->validate([
            'key' => 'required|string' . $uniqueKeyRule,
            'slug' => 'nullable|string',
            'active' => 'nullable|boolean',
            'sort' => 'nullable|integer',
            'data' => 'nullable|array',
            'locales' => 'nullable|array',
        ]);

        return response()->json($this->repository->updateModel($businessId, $id, $request->all()));
    }

    public function destroy($businessId, $id): JsonResponse
    {
        return response()->json($this->repository->destroy($businessId, $id));
    }

    public function sort(Request $request, $businessId): JsonResponse
    {
        $request->validate(['sortedIds' => 'required|array']);

        return response()->json($this->repository->sort($businessId, $request->all()));
    }
}
