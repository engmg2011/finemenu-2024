<?php

namespace App\Services\PaymentProviders;

use App\Constants\PaymentConstants;
use App\Events\UpdateOrder;
use App\Events\UpdateReservation;
use App\Jobs\SendUpdateOrderNotification;
use App\Jobs\SendUpdateReservationNotification;
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

    public function checkout($referenceNumber, $callBackUrl)
    {
        $callBackUrlEncrypted = encrypt([
            'callbackUrl' => $callBackUrl
        ]);

        $invoice = Invoice::with('forUser')
            ->where('reference_id', $referenceNumber)->first();
        $paymentData = [
            "merchantCode" => env('HESABE_MERCHANT_CODE'),
            "amount" => $invoice->amount,
            "paymentType" => "0",
            "responseUrl" => route('payment.hesabe-completed', ['referenceId' => $referenceNumber , 'encryptedData' => $callBackUrlEncrypted]),
            "failureUrl" => route('payment.failed', ['encryptedData' => $callBackUrlEncrypted]),
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
            \Log::critical("Error: Invalid data received : " . $encryptedData);
            return redirect()->route('payment.failed');
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
                    $invoice->update([
                        'status' => PaymentConstants::INVOICE_PAID,
                        'paid_at' => now(),
                    ]);
                    if ($invoice->reservation) {
                        $invoice->reservation->update(['status' => PaymentConstants::RESERVATION_COMPLETED]);

                        app('App\Repository\Eloquent\ReservationRepository')->setReservationCashedData($invoice->reservation->id);
                        event(new UpdateReservation($invoice->reservation_id));
                        // todo :: make it update
                        dispatch(new SendUpdateReservationNotification($invoice->reservation->id));
                    }
                    if ($invoice->order) {
                        $invoice->order->update(['paid' => true]);
                        // todo :: check if required to update order cached data
                        event(new UpdateOrder($invoice->order_id));
                        // todo :: make it update
                        dispatch(new SendUpdateOrderNotification($invoice->order_id));
                    }
                    return redirect()->route('payment.success' , ['encryptedData' => $request->query('encryptedData')]);
                }
                \Log::info("Invoice checked while it's paid");
                return redirect()->route('payment.success');
            }
        }
        return redirect()->route('payment.failed');
    }


}
