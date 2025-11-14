<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Repository\FeatureRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeaturesController extends Controller
{
    public function __construct(private FeatureRepositoryInterface $repository)
    {
    }

    public function index()
    {
        $itemable_type = request()->get('itemable_type', null);
        return DataResource::collection($this->repository->listModel($itemable_type));
    }

    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'key' => 'required|string|unique:features,key',
            'type' => 'required|string',
        ]);
        return response()->json($this->repository->createModel($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        return response()->json($this->repository->get($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $feature = Feature::findOrFail($id);
        if ($request->key !== $feature->key)
            $request->validate(['key' => 'required|string|unique:features,key']);
        return response()->json($this->repository->updateModel($id, $request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        return response()->json($this->repository->destroy($id));
    }

    public function sort(Request $request)
    {
        return response()->json($this->repository->sort($request->all()));
    }
}
