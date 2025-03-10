<?php

namespace App\Http\Controllers;

use App\Constants\AuditServices;
use App\Http\Resources\DataResource;
use App\Models\Invoice;
use App\Repository\Eloquent\ReservationRepository;
use App\Repository\InvoiceRepositoryInterface;
use App\Services\AuditService;
use Illuminate\Http\Request;

class InvoicesController extends Controller
{

    public function __construct(protected InvoiceRepositoryInterface $repository)
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

    public function userInvoices()
    {
        $conditions = [['invoice_for_id' => auth('sanctum')->id()]];
        return DataResource::collection($this->repository->list($conditions));
    }

    public function show($id)
    {
        return \response()->json($this->repository->get($id));
    }

    public function showForInvoiceOwner($id)
    {
        if( Invoice::find($id)->invoice_for_id !==  auth('sanctum')->user()->id) {
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


    public function update(Request $request)
    {
        return \response()->json($this->repository->update(\request()->route('id'), $request->all()));
    }


    public function destroy($businessId, $branchId, $id)
    {
        $invoice = Invoice::find($id);
        $reservation_id = $invoice->reservation_id;
        $deleted = $this->repository->delete($id);
        AuditService::log(AuditServices::Invoices, $id,"Deleted invoice " . $invoice->id,
            $invoice->business_id, $invoice->branch_id);
        app(ReservationRepository::class)->setReservationInvoicesCashedData($reservation_id);
        return \response()->json($deleted);
    }
}
