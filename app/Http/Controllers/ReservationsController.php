<?php

namespace App\Http\Controllers;

use App\Constants\BusinessTypes;
use App\Constants\PermissionActions;
use App\Constants\PermissionServices;
use App\Http\Resources\DataResource;
use App\Models\Business;
use App\Models\Reservation;
use App\Repository\ReservationRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReservationsController extends Controller
{

    private $businessId;
    private $branchId;

    public function __construct(protected ReservationRepositoryInterface $repository)
    {
    }

    public function index()
    {
        $branchId = request()->route('branchId');
        $businessId = request()->route('businessId');
        $user = auth('sanctum')->user();
        checkUserPermission($user, $branchId,
            PermissionServices::Reservations, PermissionActions::Read);
        $list = $this->repository->listModel($businessId, $branchId);
        return DataResource::collection($list);
    }

    public function filter(Request $request)
    {
        $user = auth('sanctum')->user();
        $branchId = request()->route('branchId');
        checkUserPermission($user, $branchId,
            PermissionServices::Reservations, PermissionActions::Read);
        $ordersList = $this->repository->filter($request);
        return DataResource::collection($ordersList);
    }

    public function filterWebApp(Request $request)
    {
        $ordersList = $this->repository->filter($request);
        return DataResource::collection($ordersList);
    }

    public function isAvailable(Request $request)
    {
        $branchId = request()->route('branchId');
        $businessId = request()->route('businessId');

        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);
        $data = $request->all();
        $this->repository->checkAllowedReservationUnits($data, $businessId, $branchId);
        return \response()->json(true);
    }

    public function userReservations()
    {
        $branchId = request()->route('branchId');
        $businessId = request()->route('businessId');
        $conditions = [['reserved_for_id' => auth('sanctum')->id()]];
        return DataResource::collection($this->repository->listModel($businessId, $branchId, $conditions));
    }

    public function show()
    {
        $id = \request()->route('id');
        return \response()->json($this->repository->get($id));
    }

    public function showForReservationOwner($id)
    {
        if (Reservation::find($id)->reserved_for_id !== auth('sanctum')->user()->id) {
            return response()->json(['message' => 'Unauthorized'], \Symfony\Component\HttpFoundation\Response::HTTP_UNAUTHORIZED);
        }
        return \response()->json($this->repository->get($id));
    }

    private function prepareData(&$data)
    {
        $data['branch_id'] = request()->route('branchId');
        $data['business_id'] = request()->route('businessId');
        $user = auth('sanctum')->user();

        checkUserPermission($user, $data['branch_id'],
            PermissionServices::Reservations, PermissionActions::Create);
        if (isset($data['invoices']))
            checkUserPermission($user, $data['branch_id'],
                PermissionServices::Invoices, PermissionActions::Create);

        $business = Business::find($data['business_id']);

        $data['from'] = businessToUtcConverter($data['from'], $business);
        $data['to'] = businessToUtcConverter($data['to'], $business);

        $data['reserved_by_id'] = auth('sanctum')->user()->id;
        $data['reserved_for_id'] = request()->get('reserved_for_id') ?? auth('sanctum')->user()->id;
    }


    public function validateData()
    {
        request()->validate([
            'follower_id' => 'required|integer|exists:users,id',
            'status' => 'required|string|max:50',
            'from' => 'required|date|date_format:Y-m-d H:i:s',
            'to' => 'required|date|date_format:Y-m-d H:i:s',
            'reserved_for_id' => 'required|integer|exists:users,id',
            'invoices.*.invoice_for_id' => 'required|integer|exists:users,id',
            'invoices.*.amount' => 'required|numeric',
            'invoices.*.type' => 'nullable|string|max:50',
            'invoices.*.status' => 'nullable|string|max:50',
            'invoices.*.payment_type' => 'nullable|string|max:50',
            'invoices.*.note' => 'nullable|string|max:250',
            'invoices.*.description' => 'nullable|string|max:2500',
        ],
        [
            'invoices.*.amount' => 'Invalid invoice amount.',
            'invoices.*.type' => 'Invoice type must be one of predefined values.',
            'invoices.*.status' => 'Status description must be one of predefined values.',
            'invoices.*.payment_type' => 'Payment type must be one of predefined values.',
            'invoices.*.note' => 'Note must not exceed 250 characters.',
            'invoices.*.description.max' => 'Invoice description must not exceed 2500 characters.',
        ]);
    }

    public function create(Request $request)
    {
        $data = $request->all();
        $this->prepareData($data);

        request()->merge(['reserved_for_id' => $data['reserved_for_id'], 'reserved_by_id' => $data['reserved_by_id']]);

        $this->validateData();

        return \response()->json($this->repository->create($data));
    }


    public function branchList($businessId)
    {
        $branchId = \request()->route('branchId');
        return DataResource::collection($this->repository->list([["branch_id" => $branchId]]));
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
        $id = \request()->route('id');
        $data = $request->all();
        $this->prepareData($data);
        return \response()->json($this->repository->updateModel($id, $data));
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
        $user = auth('sanctum')->user();
        checkUserPermission($user, \request()->route('branchId'),
            PermissionServices::Reservations, PermissionActions::Delete);
    }


    public function filterReservables(Request $request){
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date',
        ]);
        $data = $request->all();
        return response()->json($this->repository->filterReservables($data));
    }
}
