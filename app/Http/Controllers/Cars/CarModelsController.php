<?php

namespace App\Http\Controllers\Cars;

use App\Http\Controllers\Controller;
use App\Http\Resources\DataResource;
use App\Repository\ItemableInterfaces\CarBrandRepositoryInterface;
use App\Repository\ItemableInterfaces\CarModelRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarModelsController extends Controller
{
    public function __construct(private CarModelRepositoryInterface $repository)
    {
    }

    public function index($brand_id)
    {
        return DataResource::collection($this->repository->listModel($brand_id));
    }

    public function show( $brandId, $id)
    {
        return response()->json($this->repository->get($id));
    }

    public function create(Request $request): JsonResponse
    {
        return response()->json($this->repository->createModel($request->all()));
    }

    public function update(Request $request, $brandId, $id)
    {
        return response()->json($this->repository->updateModel($id, $request->all()));
    }

    public function destroy( $brandId, $id)
    {
        return response()->json($this->repository->destroy($id));
    }

    public function sort(Request $request)
    {
        return response()->json($this->repository->sort($request->all()));
    }

}
