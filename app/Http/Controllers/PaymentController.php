<?php

namespace App\Http\Controllers;

use App\Services\PaymentProviders\Hesabe;
use App\Services\PaymentProviders\PaymentService;

class PaymentController extends Controller
{
    private PaymentService  $paymentService;

    public function createLink($referenceNumber)
    {
        return $this->paymentService->createLink($referenceNumber);
    }


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

    public function hesabeCompleted($referenceNumber)
    {
        $this->paymentService = new PaymentService(new Hesabe());
        $this->paymentService->completed($referenceNumber);
        return response()->json(['success' => true, 'message' => 'Thank you for informing us']);
    }

    public function success()
    {
        $this->paymentService->success();
    }

    public function failure()
    {
        $this->paymentService->failure();
    }


}
