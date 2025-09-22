<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Repository\SeatRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SeatsController extends Controller
{
    public function __construct(private SeatRepositoryInterface $repository)
    {
    }

    public function index(Request $request)
    {
        return DataResource::collection($this->repository->areaSeats($request->route('areaId')));
    }

    public function createModel(Request $request)
    {
        return response()->json($this->repository->createModel($request->route('areaId'), $request->all()));
    }

    public function show(Request $request)
    {
        return response()->json($this->repository->get($request->route('areaId'), $request->route('id')));
    }

    public function update(Request $request)
    {
        return response()->json($this->repository->updateModel($request->route('areaId'), $request->route('id'), $request->all()));
    }

    public function destroy(Request $request)
    {
        return response()->json($this->repository->destroy($request->route('areaId'), $request->route('id')));
    }

    public function sort(Request $request)
    {
        return response()->json($this->repository->sort($request->route('areaId'), $request->all()));
    }
}
