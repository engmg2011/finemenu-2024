<?php

namespace App\Services\PaymentProviders;

use App\Constants\MobileAppSettings;
use App\Constants\PaymentConstants;
use App\Events\UpdateOrder;
use App\Events\UpdateReservation;
use App\Jobs\SendUpdateOrderNotification;
use App\Jobs\SendUpdateReservationNotification;
use App\Models\Invoice;
use App\Repository\Eloquent\SettingRepository;
use Hesabe\Payment\HesabeCrypt;
use Hesabe\Payment\Payment;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Log;

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

        $paymentHintSetting = app(SettingRepository::class)->getMobileAppSettingByKey( $invoice->branch_id,MobileAppSettings::PaymentHint);;
        $paymentHint = $paymentHintSetting ? (___( $paymentHintSetting, \App::getLocale())["description"] ?? null) : null;

        if (!$invoice) {
            \Log::error("Invoice Not found for reference ".$referenceNumber );
            abort(400, "Invoice Not found for reference ".$referenceNumber );
        }
        if(!$invoice->forUser){
            \Log::error("Invoice for user Not found for reference ".$referenceNumber );
        }

        $paymentData = [
            "merchantCode" => env('HESABE_MERCHANT_CODE'),
            "amount" => $invoice->amount,
            "paymentType" => "0",
            "responseUrl" => route('payment.hesabe-completed', ['referenceId' => $referenceNumber , 'encryptedData' => $callBackUrlEncrypted]),
            "failureUrl" => route('payment.failed', ['encryptedData' => $callBackUrlEncrypted]),
            "orderReferenceNumber" => "" . $referenceNumber,
            "variable1" => null,
            "version" => "3.0",
            "name" => $invoice->forUser?->name,
            "mobile_number" => $invoice->forUser?->phone,
            "email" => $invoice->forUser?->email,
            "webhookUrl" => route('payment.hesabe-webhook-completed', ['referenceId' => $referenceNumber]),
            'description' => $invoice->description ?? $paymentHint ?? "",
        ];
        \Log::debug(json_encode($paymentData));
        return $this->payment->checkout($paymentData);
    }

    public function completed($request, $referenceNumber)
    {
        \Log::debug(json_encode([
            "completed called",
            "referenceNumber" => $referenceNumber,
            $request->all()]));

        // Step 1: Get encrypted data from Hesabe
        $encryptedData = $request->input('data'); // The 'data' field contains the encrypted payload

        // Step 2: Decrypt the data
        $hesabeService = new HesabeCrypt(); // Example service for decryption
        $decryptedData = $hesabeService->decrypt($encryptedData,
            env('HESABE_SECRET_KEY'), env('HESABE_IV_KEY'));

        $decryptedDataObj = json_decode($decryptedData);

        // Step 3: Validate the response
        if (!$decryptedDataObj || !isset($decryptedDataObj->response)) {
            if($encryptedData !== "" && $encryptedData !== null)
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
                return redirect()->route('payment.success' , ['encryptedData' => $request->query('encryptedData')]);
            }
        }
        return redirect()->route('payment.failed');
    }

    public function hesabeWebhookCompleted(Request $request, string $referenceId)
    {
        Log::info('Hesabe webhook received', [
            'referenceId' => $referenceId,
            'payload' => $request->all(),
            'headers' => $request->header(),
        ]);

        try {
            // Step 1: Verify HMAC signature
            if (!$this->verifyWebhookSignature($request)) {
                Log::error('Webhook signature verification failed', [
                    'referenceId' => $referenceId,
                ]);
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $reference = $request->input('reference_number');
            $status = $request->input('status');
            if (!$reference) {
                Log::error('Webhook missing reference_number');
                return response()->json(['success' => false], 400);
            }

            $invoice = Invoice::where('reference_id', $reference)->first();
            if (!$invoice) {
                Log::error('Invoice not found', [
                    'reference' => $reference,
                ]);
                return response()->json(['success' => false], 404);
            }

            // Store the raw webhook payload for debugging
            $invoice->update([
                'data' => $request->all(),
            ]);

            if ($status !== 'SUCCESSFUL') {
                Log::info('Payment not successful', [
                    'reference' => $reference,
                    'status' => $status,
                ]);
                return response()->json(['success' => true]);
            }

            $processed = DB::transaction(function () use ($invoice) {
                $invoice = Invoice::with(['reservation', 'order'])
                    ->lockForUpdate()
                    ->find($invoice->id);
                if ($invoice->status === PaymentConstants::INVOICE_PAID) {
                    return false;
                }
                $invoice->update([
                    'status' => PaymentConstants::INVOICE_PAID,
                    'paid_at' => now(),
                ]);
                if ($invoice->reservation) {
                    $invoice->reservation->update([
                        'status' => PaymentConstants::RESERVATION_COMPLETED,
                    ]);
                    app('App\Repository\Eloquent\ReservationRepository')
                        ->setReservationCashedData($invoice->reservation->id);
                }
                if ($invoice->order) {
                    $invoice->order->update([
                        'paid' => true,
                    ]);
                }
                return true;
            });

            if ($processed) {
                if ($invoice->reservation) {
                    event(new UpdateReservation($invoice->reservation_id));
                    dispatch(new SendUpdateReservationNotification($invoice->reservation->id));
                }
                if ($invoice->order) {
                    event(new UpdateOrder($invoice->order_id));
                    dispatch(new SendUpdateOrderNotification($invoice->order_id));
                }
                Log::info('Invoice successfully processed', [
                    'reference' => $reference,
                ]);
            } else {
                Log::info('Invoice already processed', [
                    'reference' => $reference,
                ]);
            }
            return response()->json(['success' => true]);

        } catch (\Throwable $e) {
            Log::error('Webhook processing failed', [
                'referenceId' => $referenceId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Verify webhook signature using HMAC-SHA256
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    private function verifyWebhookSignature($request): bool
    {
        // Get the signature from the header
        $receivedSignature = $request->header('X-Signature');

        if (!$receivedSignature) {
            Log::error('X-Signature header missing');
            return false;
        }

        // Get the access code (webhook secret)
        $webhookSecret = env('HESABE_ACCESS_CODE');

        // Build the HMAC string by concatenating all top-level payload fields except 'blocks'
        $payload = $request->all();
        $macString = '';
        foreach ($payload as $key => $value) {
            if ($key !== 'blocks') {
                // Hesabe expects value as string; flatten arrays/objects if any
                if (is_array($value) || is_object($value)) {
                    $macString .= json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                } elseif ($value === null) {
                    $macString .= '';
                } else {
                    $macString .= (string)$value;
                }
            }
        }

        // Generate HMAC-SHA256 hash
        $computedSignature = hash_hmac('sha256', $macString, $webhookSecret);

        Log::debug('HMAC Verification Debug', [
            'receivedSignature' => $receivedSignature,
            'computedSignature' => $computedSignature,
            'macString' => $macString,
            'payloadKeys' => array_keys($payload),
        ]);

        // Compare signatures securely
        return hash_equals($computedSignature, $receivedSignature);
    }
}
