<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Repository\Eloquent\CategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use function response;

class CategoriesController extends Controller
{

    public function __construct(private CategoryRepository $repository)
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
    public function create(Request $request)
    {
        return response()->json($this->repository->createModel($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($businessId, $id)
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
    public function update(Request $request, $businessId, $id)
    {
        return response()->json($this->repository->updateModel($id, $request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($businessId, $id)
    {
        return response()->json($this->repository->destroy($id));
    }

    public function updateSort(Request $request)
    {
        return response()->json($this->repository->updateSort($request->all()));
    }
}
