<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Models\Business;
use App\Repository\AreaRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class AreasController extends Controller
{
    public function __construct(private AreaRepositoryInterface $repository)
    {
    }

    public function index($businessId, $branchId)
    {
        return DataResource::collection($this->repository->branchAreas($businessId, $branchId));
    }

    public function createModel(Request $request, $businessId, $branchId): JsonResponse
    {
        return response()->json($this->repository->createModel($businessId, $branchId, $request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($businessId, $branchId, $id)
    {
        return response()->json($this->repository->get($businessId, $branchId,$id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $businessId, $branchId, $id)
    {
        return response()->json($this->repository->updateModel($businessId, $branchId, $id, $request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($businessId, $branchId, $id)
    {
        return response()->json($this->repository->destroy($businessId, $branchId, $id));
    }

    public function sort(Request $request, $businessId, $branchId)
    {
        return response()->json($this->repository->sort($businessId, $branchId, $request->all()));
    }
}
