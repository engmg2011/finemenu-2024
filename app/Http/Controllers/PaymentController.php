<?php

namespace App\Http\Controllers;

use App\Services\PaymentProviders\Hesabe;
use App\Services\PaymentProviders\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{

    public function __construct(private PaymentService  $paymentService = new PaymentService())
    {
    }

    public function hesabeCheckout($referenceNumber)
    {
        $this->paymentService = new PaymentService(new Hesabe());
        $this->paymentService->checkout($referenceNumber);
        exit;
    }

    public function hesabeCompleted(Request $request ,$referenceNumber)
    {
        $this->paymentService = new PaymentService(new Hesabe());
        return $this->paymentService->completed($request, $referenceNumber);
    }

    public function success()
    {
        $data = ["msg" => "Completed Successfully", "color" =>"green"];
        return view('payment.success', $data);
    }

    public function failed()
    {
        $data = ["msg" => "Error: Invalid data received", "color" =>"red"];
        return view('payment.failed', $data);
    }


}
