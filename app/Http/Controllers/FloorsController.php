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
    public function createModel(Request $request)
    {
        return response()->json($this->repository->createModel($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
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
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        return response()->json($this->repository->update($id, $request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
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

    public function branchFloors($restaurantId , $branchId)
    {
        return DataResource::collection($this->repository->branchFloors($restaurantId, $branchId));
    }

}
