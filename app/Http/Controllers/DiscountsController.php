<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Models\Business;
use App\Repository\DiscountRepositoryInteface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class DiscountsController extends Controller
{

    public function __construct(private DiscountRepositoryInteface $repository)
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
        $data = $request->all();
        $business = Business::find($data['business_id']);
        $data['from'] = businessToUtcConverter($data['from'], $business,'Y-m-d H:i:s');
        $data['to'] = businessToUtcConverter($data['to'], $business,'Y-m-d H:i:s');
        return \response()->json($this->repository->createModel($data));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        return \response()->json($this->repository->get($id));
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
        $data = $request->all();
        $business = Business::find($data['business_id']);
        $data['from'] = businessToUtcConverter($data['from'], $business,'Y-m-d H:i:s');
        $data['to'] = businessToUtcConverter($data['to'], $business,'Y-m-d H:i:s');
        return \response()->json($this->repository->updateModel($id, $data));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        return response()->json($this->repository->destroy($id));
    }
}
