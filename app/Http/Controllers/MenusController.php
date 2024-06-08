<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Repository\MenuRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MenusController extends Controller
{
    public function __construct(private MenuRepositoryInterface $repository)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index($restaurantId)
    {
        return DataResource::collection($this->repository->listModel($restaurantId));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createModel(Request $request, $restaurantId)
    {
        return response()->json($this->repository->createModel($restaurantId, $request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($restaurantId, $id)
    {
        return response()->json($this->repository->get($restaurantId, $id));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function menu($id)
    {
        return response()->json($this->repository->fullMenu($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $restaurantId, $id)
    {
        return response()->json($this->repository->updateModel($restaurantId, $id, $request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($restaurantId, $id)
    {
        return response()->json($this->repository->destroy($restaurantId, $id));
    }

    public function sort(Request $request, $restaurantId)
    {
        return response()->json($this->repository->sort($restaurantId, $request->all()));
    }
}
