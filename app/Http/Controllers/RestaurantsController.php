<?php

namespace App\Http\Controllers;

use App\Actions\RestaurantAction;
use App\Http\Resources\DataResource;
use App\Http\Resources\RestaurantsResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RestaurantsController extends Controller
{
    public function __construct(private RestaurantAction $action)
    {
    }

    public function menu($restaurantId) {
        return response()->json($this->action->menu($restaurantId));
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return DataResource::collection($this->action->list());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        return \response()->json($this->action->createModel($request->all() + [
            "name" => $request->name,
            "user_id" => $request->user_id,
            "creator_id" => auth('api')->user()->id ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        return \response()->json($this->action->getModel($id));
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
        return \response()->json($this->action->updateModel($id, $request->all() + [
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
        //
    }
}
