<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Models\Business;
use App\Repository\HolidayRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HolidaysController extends Controller
{
    public function __construct(private HolidayRepositoryInterface $repository)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index($businessId)
    {
        return DataResource::collection($this->repository->listModel($businessId));
    }
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function filter($businessId)
    {
        return response()->json($this->repository->filter($businessId));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createModel(Request $request, $businessId)
    {
        $data = $request->all();
        $business = Business::find($businessId);
        $data['from'] = businessToUtcConverter($data['from'], $business,'Y-m-d H:i:s');
        $data['to'] = businessToUtcConverter($data['to'], $business,'Y-m-d H:i:s');
        return response()->json($this->repository->createModel($businessId, $data));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($businessId, $id)
    {
        return response()->json($this->repository->get($businessId, $id));
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
        $data = $request->all();
        $business = Business::find($businessId);
        $data['from'] = businessToUtcConverter($data['from'], $business,'Y-m-d H:i:s');
        $data['to'] = businessToUtcConverter($data['to'], $business,'Y-m-d H:i:s');
        return response()->json($this->repository->updateModel($businessId, $id, $data));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($businessId, $id)
    {
        return response()->json($this->repository->destroy($businessId, $id));
    }

}
