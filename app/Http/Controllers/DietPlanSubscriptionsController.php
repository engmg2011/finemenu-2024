<?php

namespace App\Http\Controllers;

use App\Actions\DietPlanSubscriptionAction;
use App\Http\Resources\DataResource;
use App\Repository\Eloquent\DietPlanRepository;
use App\Repository\Eloquent\DietPlanSubscriptionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use function response;

class DietPlanSubscriptionsController extends Controller
{

    public function __construct(private DietPlanSubscriptionRepository $repository)
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
        return response()->json($this->repository->create($request->all()));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function subscribe(Request $request, $diet_plan_id)
    {
        $request->request->add(['diet_plan_id'=>$diet_plan_id]);
        return response()->json($this->repository->create($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        return response()->json($this->repository->get($id));
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
        return response()->json($this->repository->update($id,$request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        return response()->json($this->repository->destroy($id));
    }
}
