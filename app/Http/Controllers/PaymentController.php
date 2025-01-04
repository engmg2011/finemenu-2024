<?php

namespace App\Http\Controllers;

use App\Services\PaymentProviders\Hesabe;
use App\Services\PaymentProviders\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    private PaymentService  $paymentService;

    public function completed($referenceNumber)
    {
        $this->paymentService->checkout($referenceNumber);
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

    public function failed()
    {
        $this->paymentService = new PaymentService(new Hesabe());
        $this->paymentService->failed();
    }


}
