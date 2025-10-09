<?php

namespace App\Http\Controllers;

use App\Constants\PaymentConstants;
use App\Models\Invoice;
use App\Services\PaymentProviders\Hesabe;
use App\Services\PaymentProviders\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{

    public function __construct(private PaymentService  $paymentService = new PaymentService())
    {
    }

    public function isPendingInvoice($referenceNumber)
    {
        $invoiceStatus = Invoice::where('reference_id', $referenceNumber)->pluck('status')->first();
        return $invoiceStatus === PaymentConstants::INVOICE_PENDING;
    }

    public function hesabeCheckout($referenceNumber)
    {
        $this->paymentService = new PaymentService(new Hesabe());
        // disable multiple payment
        if(!$this->isPendingInvoice($referenceNumber))
            return redirect()->route('invoice.show', $referenceNumber);
        $checkoutLink = $this->paymentService->checkout($referenceNumber);
        return redirect($checkoutLink);
    }

    public function hesabeCompleted(Request $request ,$referenceNumber)
    {
        $this->paymentService = new PaymentService(new Hesabe());
        return $this->paymentService->completed($request, $referenceNumber);
    }

    public function success()
    {
        $data = ["msg" => "Completed Successfully", "color" =>"green"];
        $callback = session('paymentCallback');
        if(isset($callback) && $callback !== ''){
            session()->forget('paymentCallback');
            return redirect($callback . '?success=true' );
        }
        return view('payment.success', $data);
    }

    public function failed()
    {
        $data = ["msg" => "Error: Invalid data received", "color" =>"red"];
        $callback = session('payment-callback');
        if(isset($callback) && $callback !== ''){
            session()->forget('payment-callback');
            return redirect($callback . '?success=false' );
        }
        return view('payment.failed', $data);
    }


}
