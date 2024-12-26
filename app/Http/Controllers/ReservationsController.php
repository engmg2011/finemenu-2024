<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Models\Reservation;
use App\Repository\ReservationRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReservationsController extends Controller
{

    public function __construct(protected ReservationRepositoryInterface $reservationRepository)
    {
    }

    public function index()
    {
        $ordersList = $this->reservationRepository->list();
        return DataResource::collection($ordersList);
    }

    public function userReservations()
    {
        $conditions = [['reserved_for_id' => auth('sanctum')->id()]];
        return DataResource::collection($this->reservationRepository->list($conditions));
    }

    public function show($id)
    {
        return \response()->json($this->reservationRepository->get($id));
    }

    public function showForReservationOwner($id)
    {
        if( Reservation::find($id)->reserved_for_id !==  auth('sanctum')->user()->id) {
            return response()->json(['message' => 'Unauthorized'], \Symfony\Component\HttpFoundation\Response::HTTP_UNAUTHORIZED);
        }
        return \response()->json($this->reservationRepository->get($id));
    }

    public function create(Request $request)
    {
        return \response()->json($this->reservationRepository->create($request->all()));
    }


    public function branchOrders($businessId)
    {
        $branchId = \request()->route('branchId');
        return DataResource::collection($this->reservationRepository->branchOrders($branchId));
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
        return \response()->json($this->reservationRepository->update(\request()->route('id'), $request->all()));
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
