<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Repository\FloorRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FloorsController extends Controller
{
    public function __construct(private FloorRepositoryInterface $repository)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return DataResource::collection($this->repository->list());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createModel(Request $request, $restaurantId, $branchId)
    {
        return response()->json($this->repository->createModel($restaurantId, $branchId, $request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($restaurantId, $branchId, $id)
    {
        return response()->json($this->repository->get($restaurantId, $branchId,$id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $restaurantId, $branchId, $id)
    {
        return response()->json($this->repository->updateModel($restaurantId, $branchId, $id, $request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($restaurantId, $branchId, $id)
    {
        return response()->json($this->repository->destroy($restaurantId, $branchId, $id));
    }

    public function sort(Request $request, $restaurantId, $branchId)
    {
        return response()->json($this->repository->sort($restaurantId, $branchId, $request->all()));
    }

    public function branchFloors($restaurantId, $branchId)
    {
        return DataResource::collection($this->repository->branchFloors($restaurantId, $branchId));
    }

}
