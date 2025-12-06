<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Repository\CategoryRepositoryInterface;
use Illuminate\Http\Request;
use function response;

class CategoriesController extends Controller
{

    public function __construct(private CategoryRepositoryInterface $repository)
    {
    }

    public function index()
    {
        return DataResource::collection($this->repository->list());
    }

    public function create(Request $request)
    {
        return response()->json($this->repository->createModel($request->all()));
    }

    public function show($businessId, $id)
    {
        return response()->json($this->repository->get($id));
    }

    public function update(Request $request, $businessId, $id)
    {
        return response()->json($this->repository->updateModel($id, $request->all()));
    }

    public function updateFeatureCategory(Request $request, $id)
    {
        return response()->json($this->repository->updateModel($id, $request->all()));
    }

    public function destroy($businessId, $id)
    {
        return response()->json($this->repository->destroy($businessId, $id));
    }

    public function destroyFeatureCategory($id)
    {
        return response()->json($this->repository->destroy($id));
    }

    public function updateSort(Request $request)
    {
        return response()->json($this->repository->updateSort($request->all()));
    }

    public function featuresCategories()
    {
        return response()->json($this->repository->featuresCategories());
    }
}
