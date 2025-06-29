<?php

namespace App\Http\Controllers;

use App\Constants\AuditServices;
use App\Http\Resources\DataResource;
use App\Models\Invoice;
use App\Repository\Eloquent\InvoiceRepository;
use App\Repository\Eloquent\ReservationRepository;
use App\Repository\InvoiceRepositoryInterface;
use App\Services\AuditService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function showInvoice($referenceId)
    {
        $invoice = Invoice::with(InvoiceRepository::Relations)
            ->where('reference_id', $referenceId)->firstOrFail();
        return view('invoice', compact('invoice'));
    }

    public function download($referenceId){
        $invoice = Invoice::with(InvoiceRepository::Relations)
            ->where('reference_id', $referenceId)->firstOrFail();

        // Generate PDF content
        $pdf = Pdf::loadView('invoice', compact('invoice'));

        // Creation filename
        $fileName = 'invoice_' . $invoice->reference_id . '.pdf';

        Storage::disk('public')->put('invoices/' . $fileName, $pdf->output());
        return url('storage/invoices/' . $fileName);
    }
}
