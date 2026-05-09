<?php

namespace App\Http\Controllers;

use App\Constants\RolesConstants;
use App\Http\Resources\DataResource;
use App\Models\Business;
use App\Models\Order;
use App\Repository\OrderRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class OrdersController extends Controller
{
    private $businessId;
    private $branchId;
    private $business;

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

    function datesConversion(&$data)
    {
        if (isset($data['order_lines']) && is_array($data['order_lines'])) {
            for ($i = 0; $i < count($data['order_lines']); $i++) {
                if (isset($data['order_lines'][$i]['reservation'])) {
                    $data['order_lines'][$i]['reservation']['from'] =
                        businessToUtcConverter($data['order_lines'][$i]['reservation']['from'], $this->business);
                    $data['order_lines'][$i]['reservation']['to'] =
                        businessToUtcConverter($data['order_lines'][$i]['reservation']['to'], $this->business);
                }
            }
        }
    }

    public function validateCreation()
    {
//        \request()->validate([
//            'order_lines' => 'required|array',
//            'order_lines.*.item_id' => 'required|exists:items,id',
//            'order_lines.*.count' => 'required|integer|min:1',
//            'order_lines.*.reservation' => 'nullable|array',
//            'order_lines.*.reservation.from' => 'required|date',
//            'order_lines.*.reservation.to' => 'required|date|after:order_lines.*.reservation.from',
//        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $this->validateCreation();

        // todo:: check if not exceeds BusinessSettings['EnableReservationsTill'] using getBusinessSettingByKey('EnableReservationsTill')
        $data = $request->all();
        $data['business_id'] = request()->route('businessId');
        $data['branch_id'] = request()->route('branchId');
        $this->businessId = $data['business_id'];
        $this->business = Business::find($data['business_id']);
        $this->branchId = $data['branch_id'];
        $this->datesConversion($data);

        return \response()->json($this->orderRepository->create($data, $this->business));
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
        if (Order::find($id)->user_id !== auth('sanctum')->user()->id) {
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
