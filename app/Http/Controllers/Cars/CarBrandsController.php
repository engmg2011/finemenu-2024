<?php

namespace App\Http\Controllers\Cars;

use App\Http\Controllers\Controller;
use App\Http\Resources\DataResource;
use App\Repository\ItemableInterfaces\CarBrandRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarBrandsController extends Controller
{
    public function __construct(private CarBrandRepositoryInterface $repository)
    {
    }

    public function index()
    {
        return DataResource::collection($this->repository->listModel());
    }

    public function show($id)
    {
        return response()->json($this->repository->get($id));
    }

    public function create(Request $request): JsonResponse
    {
        return response()->json($this->repository->createModel($request->all()));
    }

    public function update(Request $request, $id)
    {
        return response()->json($this->repository->updateModel($id, $request->all()));
    }

    public function destroy($id)
    {
        return response()->json($this->repository->destroy($id));
    }

    public function sort(Request $request)
    {
        return response()->json($this->repository->sort($request->all()));
    }

}
