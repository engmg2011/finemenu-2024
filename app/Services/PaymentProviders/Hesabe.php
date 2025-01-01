<?php

namespace App\Services\PaymentProviders;

use App\Constants\PaymentConstants;
use App\Models\Invoice;
use Hesabe\Payment\Payment;

class Hesabe implements PaymentProviderInterface
{
    private Payment $payment;

    public function __construct()
    {
        $this->payment = new Payment(
            env('HESABE_SECRET_KEY'),
            env('HESABE_IV_KEY'),
            env('HESABE_ACCESS_CODE'),
            env('HESABE_SANDBOX')
        );

    }

    public function createLink($referenceNumber): string
    {
        return 'https://hesabi.com.uz/payment/payment';
    }


    public function checkout($referenceNumber)
    {
        $invoice = Invoice::with('forUser')
            ->where('reference_id', $referenceNumber)->first();
        $paymentData = [
            "merchantCode" => env('HESABE_MERCHANT_CODE'),
            "amount" => $invoice->amount,
            "paymentType" => "0",
            "responseUrl" => route('payment.success', ['referenceId' => $referenceNumber]),
            "failureUrl" => route('payment.failure', ['referenceId' => $referenceNumber]),
            "orderReferenceNumber" => "".$referenceNumber,
            "variable1" => null,
            "version" => "2.0",
            "name" => $invoice->forUser->name,
            "mobile_number" => $invoice->forUser->phone,
            "email" => $invoice->forUser->email,
            "webhookUrl" => route('payment.hesabe-completed', ['referenceId' => $referenceNumber]),
        ];
        return $this->payment->checkout($paymentData);
    }

    public function completed($referenceNumber)
    {
        $invoice = Invoice::where('reference_id', $referenceNumber)->first();
        return tap($invoice)->update(['status' => PaymentConstants::INVOICE_PAID]);
       /* $invoice = Invoice::where('reference_id', $referenceNumber)->first();
        $paymentData = [
            "merchantCode" => env('HESABE_MERCHANT_CODE'),
            "amount" => $invoice->amount,
            "paymentType" => "0",
            "responseUrl" => route('payment.success', ['referenceNumber' => $referenceNumber]),
            "failureUrl" => route('payment.failure', ['referenceNumber' => $referenceNumber]),
            "orderReferenceNumber" => "".$referenceNumber,
            "variable1" => null,
            "version" => "2.0",
            "name" => "Mohamed Gamal",
            "mobile_number" => "+96565708188",
            "email" => "eng.mg2011@gmail.com",
            "webhookUrl" => route('payment.completed', ['referenceNumber' => $referenceNumber]),
        ];
        return $this->payment->checkout($paymentData);*/
    }

    public function checkPayment(): void
    {

    }

    public function success(): void
    {

    }

    public function failure(): void
    {

    }

}
