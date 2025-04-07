<?php

namespace App\Http\Controllers;

use App\Constants\PermissionActions;
use App\Constants\PermissionServices;
use App\Http\Resources\DataResource;
use App\Models\Reservation;
use App\Repository\ReservationRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReservationsController extends Controller
{

    public function __construct(protected ReservationRepositoryInterface $repository)
    {
    }

    public function index()
    {
        $branchId = request()->route('branchId');
        $businessId = request()->route('businessId');
        $ordersList = $this->repository->listModel($businessId, $branchId);
        return DataResource::collection($ordersList);
    }

    public function filter(Request $request)
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
        $this->repository->checkAllowedReservationAmount($data, $businessId, $branchId);
        return \response()->json(true);
    }

    public function userReservations()
    {
        $branchId = request()->route('branchId');
        $businessId = request()->route('businessId');
        $conditions = [['reserved_for_id' => auth('sanctum')->id()]];
        return DataResource::collection($this->repository->listModel($businessId, $branchId, $conditions));
    }

    public function show($id)
    {
        return \response()->json($this->repository->get($id));
    }

    public function showForReservationOwner($id)
    {
        if (Reservation::find($id)->reserved_for_id !== auth('sanctum')->user()->id) {
            return response()->json(['message' => 'Unauthorized'], \Symfony\Component\HttpFoundation\Response::HTTP_UNAUTHORIZED);
        }
        return \response()->json($this->repository->get($id));
    }

    public function create(Request $request)
    {
        $data = $request->all();
        $data['branch_id'] = request()->route('branchId');
        $data['business_id'] = request()->route('businessId');
        checkUserPermission(auth('sanctum')->user(), $data['branch_id'],
            PermissionServices::Reservations, PermissionActions::Create);
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
        $data['branch_id'] = request()->route('branchId');
        checkUserPermission(auth('sanctum')->user(), $data['branch_id'],
            PermissionServices::Reservations, PermissionActions::Update);
        return \response()->json($this->repository->updateModel($id, $request->all()));
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
