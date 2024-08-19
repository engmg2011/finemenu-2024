<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Repository\BusinessRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class BusinessController extends Controller
{
    public function __construct(private BusinessRepositoryInterface $repository)
    {
    }

    public function menu($businessId) {
        return response()->json($this->repository->getModel($businessId));
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
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function allList()
    {
        return DataResource::collection($this->repository->allList());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        return \response()->json($this->repository->createModel($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        return \response()->json($this->repository->getModel($id));
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
        return \response()->json($this->repository->updateModel($id, $request->all() + [
            "user_id" => auth('api')->user()->id]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $this->repository->destroy($id);
    }



}
