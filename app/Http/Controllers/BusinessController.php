<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Models\Business;
use App\Models\Locales;
use App\Models\Setting;
use App\Repository\AreaRepositoryInterface;
use App\Repository\BranchRepositoryInterface;
use App\Repository\BusinessRepositoryInterface;
use App\Repository\CategoryRepositoryInterface;
use App\Repository\InvoiceRepositoryInterface;
use App\Repository\ItemRepositoryInterface;
use App\Repository\MenuRepositoryInterface;
use App\Repository\ReservationRepositoryInterface;
use App\Repository\SeatRepositoryInterface;
use App\Repository\UserRepositoryInterface;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Storage;

class BusinessController extends Controller
{
    public function __construct(private BusinessRepositoryInterface $repository)
    {
    }

    public function menu($businessId)
    {
        return response()->json($this->repository->getModel($businessId));
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
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function businessList()
    {
        return DataResource::collection($this->repository->businessList());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        return \response()->json($this->repository->createModel($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        return \response()->json($this->repository->getModel($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        return \response()->json($this->repository->updateModel($id, $request->all() + [
                "user_id" => auth('sanctum')->user()->id]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        return \response()->json($this->repository->destroy($id));
    }

}
