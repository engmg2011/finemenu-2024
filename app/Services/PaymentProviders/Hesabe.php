<?php

namespace App\Services\PaymentProviders;

use App\Constants\PaymentConstants;
use App\Models\Invoice;
use Hesabe\Payment\HesabeCrypt;
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
            "orderReferenceNumber" => "" . $referenceNumber,
            "variable1" => null,
            "version" => "2.0",
            "name" => $invoice->forUser->name,
            "mobile_number" => $invoice->forUser->phone,
            "email" => $invoice->forUser->email,
            "webhookUrl" => route('payment.hesabe-completed', ['referenceId' => $referenceNumber]),
        ];
        return $this->payment->checkout($paymentData);
    }

    public function msg($msg, $color)
    {
        return "<h4 style='text-align: center;
                    color:$color ;
                    font-family: arial sans-serif;
                    margin: 30px;
                    text-transform: uppercase
                    '>$msg</h4>";
    }

    public function completed($request, $referenceNumber)
    {
        // Step 1: Get encrypted data from Hesabe
        $encryptedData = $request->input('data'); // The 'data' field contains the encrypted payload

        // Step 2: Decrypt the data
        $hesabeService = new HesabeCrypt(); // Example service for decryption
        $decryptedData = $hesabeService->decrypt($encryptedData,
            env('HESABE_SECRET_KEY'), env('HESABE_IV_KEY'));

        $decryptedDataObj = json_decode($decryptedData);

        // Step 3: Validate the response
        if (!$decryptedDataObj || !isset($decryptedDataObj->response)) {
            return $this->msg("Error: Invalid data received", "red");
        }

        \Log::debug(json_encode($decryptedDataObj->response));

        if (isset($decryptedDataObj->response->orderReferenceNumber)) {
            $invoice = Invoice::where(['reference_id' => $decryptedDataObj->response->orderReferenceNumber])->first();
            $invoice->update(['data' => $decryptedDataObj->response]);
        }

        // Step 4: Check payment status
        if ($decryptedDataObj->response->resultCode === 'CAPTURED') {
            \Log::debug('CAPTURED ' . $decryptedDataObj->response->orderReferenceNumber);
            // Retrieve invoice using reference or ID
            if ($invoice) {
                if ($invoice->status !== PaymentConstants::INVOICE_PAID) {
                    $invoice->update(['status' => PaymentConstants::INVOICE_PAID]);
                    return $this->msg("Completed Successfully", "green");
                }
                if ($invoice->status === PaymentConstants::INVOICE_PAID)
                    return $this->msg("The invoice already paid", "green");
            }
        }
        return $this->msg("Error: Please contact Administration", "red");
    }

    public function failed(): string
    {
        return $this->msg("Error: Please contact Administration", "red");
    }

}
