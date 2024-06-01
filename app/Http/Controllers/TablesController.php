<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Repository\TableRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TablesController extends Controller
{
    public function __construct(private TableRepositoryInterface $repository)
    {
    }

    public function index(Request $request)
    {
        return DataResource::collection($this->repository->floorTables($request->route('floorId')));
    }

    public function createModel(Request $request)
    {
        return response()->json($this->repository->createModel($request->route('floorId'), $request->all()));
    }

    public function show(Request $request)
    {
        return response()->json($this->repository->get($request->route('floorId'), $request->route('id')));
    }

    public function update(Request $request)
    {
        return response()->json($this->repository->updateModel($request->route('floorId'), $request->route('id'), $request->all()));
    }

    public function destroy(Request $request)
    {
        return response()->json($this->repository->destroy($request->route('floorId'), $request->route('id')));
    }

    public function sort(Request $request)
    {
        return response()->json($this->repository->sort($request->route('floorId'), $request->all()));
    }
}
