<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Models\Business;
use App\Repository\PageRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PagesController extends Controller
{
    public function __construct(private PageRepositoryInterface $repository)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index($businessId, $branchId)
    {
        return DataResource::collection($this->repository->listModel($businessId, $branchId));
    }
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function filter($businessId, $branchId)
    {
        return response()->json($this->repository->filter($businessId, $branchId));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createModel(Request $request, $businessId, $branchId)
    {
        $request->validate([
            'locales' => ['required', 'array', 'min:1'],
            'locales.*.locale' => ['required', 'string', 'distinct'],
            'locales.*.name' => ['required', 'string'],
            'locales.*.description' => ['nullable', 'string'],
        ]);
        $data = $request->all();
        return response()->json($this->repository->createModel($businessId, $branchId, $data));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($businessId, $branchId, $id)
    {
        return response()->json($this->repository->get($businessId, $branchId, $id));
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
        $data = $request->all();
        return response()->json($this->repository->updateModel($businessId, $branchId, $id, $data));
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

}
