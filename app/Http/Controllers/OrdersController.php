<?php

namespace App\Http\Controllers;

use App\Constants\RolesConstants;
use App\Http\Resources\DataResource;
use App\Models\Order;
use App\Repository\OrderRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class OrdersController extends Controller
{

    public function __construct(protected OrderRepositoryInterface $orderRepository)
    {
    }


    public function index()
    {
        $ordersList = match (\request()->get('for')) {
            RolesConstants::DRIVER => $this->orderRepository->driverOrders(),
            RolesConstants::CASHIER => $this->orderRepository->cashierOrders(),
            default => $this->orderRepository->list(),
        };
        return DataResource::collection($ordersList);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        return \response()->json($this->orderRepository->create($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        return \response()->json($this->orderRepository->get($id));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function showForCreator($id)
    {
        if( Order::find($id)->user_id !==  auth('sanctum')->user()->id) {
            return response()->json(['message' => 'Unauthorized'], \Symfony\Component\HttpFoundation\Response::HTTP_UNAUTHORIZED);
        }
        return \response()->json($this->orderRepository->get($id));
    }

    /**
     * @param $businessId
     * @return AnonymousResourceCollection
     */
    public function branchOrders($businessId)
    {
        $branchId = \request()->route('branchId');
        return DataResource::collection($this->orderRepository->branchOrders($branchId));
    }

    /**
     * @param $businessId
     * @return AnonymousResourceCollection
     */
    public function userOrders()
    {
        return DataResource::collection($this->orderRepository->userOrders());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        return \response()->json($this->orderRepository->update(\request()->route('id'), $request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
