<?php

namespace App\Http\Controllers;

use App;
use App\Constants\AuditServices;
use App\Constants\MobileAppSettings;
use App\Constants\PaymentConstants;
use App\Http\Resources\DataResource;
use App\Models\Invoice;
use App\Repository\Eloquent\InvoiceRepository;
use App\Repository\Eloquent\ReservationRepository;
use App\Repository\Eloquent\SettingRepository;
use App\Repository\InvoiceRepositoryInterface;
use App\Services\AuditService;
use Exception;
use Illuminate\Http\Request;

// Fixing arabic in pdf file
require_once base_path('app/ar-php/src/Arabic.php');
use ArPHP\I18N\Arabic;


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
        if (request('export') == 'true')
            return $this->repository->exportInvoices($request);

        return DataResource::collection($this->repository->getInvoices($request));
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
        $lang = request('lang' , 'en');
        App::setLocale($lang);

        $invoice = Invoice::with(InvoiceRepository::Relations)
            ->where('reference_id', $referenceId)->firstOrFail();


        $paymentHintSetting = app(SettingRepository::class)->getMobileAppSettingByKey( $invoice->branch_id,MobileAppSettings::PaymentHint);;
        $paymentHint = $paymentHintSetting ? (___( $paymentHintSetting, \App::getLocale())["description"] ?? null) : null;
        if(!$invoice->description)
            $invoice->description = $paymentHint;

        $reservation = $invoice['reservation'];
        $reservable = $invoice['reservation']['data']['reservable'];
        $divStyle = "background-color:#f0f0f0;border-radius:5px;padding:5px;margin:5px 5px;font-size: 1rem";


        $invoicesList = $invoice->reservation->invoices;
        $invoices = $invoicesList->reject(fn($inv) => $inv->id == $invoice->id)->prepend($invoice);


        $creditInvoices = $invoicesList->filter(fn($inv) => $inv->type == PaymentConstants::INVOICE_CREDIT);
        $totalCredit = $creditInvoices->sum('amount');

        $debitInvoices = $invoicesList->filter(fn($inv) => $inv->type == PaymentConstants::INVOICE_DEBIT);
        $totalDebit = $debitInvoices->sum('amount');

        $rentAmount = $totalCredit - $totalDebit;
        $logoSetting =isset($invoice->reservation->business->settings) ?
            collect($invoice->reservation->business->settings)->firstWhere('key', 'Logo') : [];

        $imageData = null;
        // create base64 image
        if(isset($logoSetting['data']) && $logoSetting['data'][0]['src']){
            $avatarUrl = $logoSetting['data'][0]['src'];
            $storageUrl = str_replace("http://", "https://", url('/storage'));
            // storage_path('app/public/10/5405_Shalehi_icon.png');
            $avatarUrl = str_replace($storageUrl, "/app/public", $avatarUrl);
            $avatarUrl = storage_path($avatarUrl);
            $arrContextOptions=array(
                "ssl"=>array(
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ),
            );
            $type = pathinfo($avatarUrl, PATHINFO_EXTENSION);
            try{
                $avatarData = file_get_contents($avatarUrl, false, stream_context_create($arrContextOptions));
                $avatarBase64Data = base64_encode($avatarData);
                $imageData = 'data:image/' . $type . ';base64,' . $avatarBase64Data;
            }catch (Exception $e){
                \Log::error("Can't get content for : ". $avatarUrl);
            }
        }
        return view('invoice', compact('invoice',
            'reservation', 'reservable', 'divStyle', 'imageData'
            , 'rentAmount', 'totalCredit', 'totalDebit', 'invoices'));
    }

    public function download($referenceId){
        $invoice = Invoice::with(InvoiceRepository::Relations)
            ->where('reference_id', $referenceId)->firstOrFail();

        $html = view('invoice', compact('invoice'))->render();

        $Arabic = new Arabic();

        $p = $Arabic->arIdentify($html);

        for ($i = count($p)-1; $i >= 0; $i-=2) {
            $utf8ar = $Arabic->utf8Glyphs(substr($html, $p[$i-1], $p[$i] - $p[$i-1]));
            $html   = substr_replace($html, $utf8ar, $p[$i-1], $p[$i] - $p[$i-1]);
        }
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($html);
        return $pdf->setOptions(['isHtml5ParserEnabled' => true, 'auto_language_detection'  => true,])
            ->download('invoice.pdf',array('Attachment'=>0));
    }

    public function arPdf()
    {
        $html = view('arPdf')->render();

        $Arabic = new Arabic();

        $p = $Arabic->arIdentify($html);

        for ($i = count($p)-1; $i >= 0; $i-=2) {
            $utf8ar = $Arabic->utf8Glyphs(substr($html, $p[$i-1], $p[$i] - $p[$i-1]));
            $html   = substr_replace($html, $utf8ar, $p[$i-1], $p[$i] - $p[$i-1]);
        }
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($html);
        return $pdf->setOptions(['isHtml5ParserEnabled' => true, 'auto_language_detection'  => true,])
            ->stream('invoice.pdf',array('Attachment'=>0));
    }
}
