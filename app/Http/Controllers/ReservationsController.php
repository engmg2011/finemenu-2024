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

    public function __construct(protected ReservationRepositoryInterface $repository)
    {
    }

    public function index()
    {
        $ordersList = $this->repository->list();
        return DataResource::collection($ordersList);
    }

    public function filter(Request $request)
    {
        $ordersList = $this->repository->filter($request);
        return DataResource::collection($ordersList);
    }

    public function userReservations()
    {
        $conditions = [['reserved_for_id' => auth('sanctum')->id()]];
        return DataResource::collection($this->repository->list($conditions));
    }

    public function show($id)
    {
        return \response()->json($this->repository->get($id));
    }

    public function showForReservationOwner($id)
    {
        if( Reservation::find($id)->reserved_for_id !==  auth('sanctum')->user()->id) {
            return response()->json(['message' => 'Unauthorized'], \Symfony\Component\HttpFoundation\Response::HTTP_UNAUTHORIZED);
        }
        return \response()->json($this->repository->get($id));
    }

    public function create(Request $request)
    {
        return \response()->json($this->repository->create($request->all()));
    }


    public function branchList($businessId)
    {
        $branchId = \request()->route('branchId');
        return DataResource::collection($this->repository->list([["branch_id" =>$branchId]]));
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
        return \response()->json($this->repository->update(\request()->route('id'), $request->all()));
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
