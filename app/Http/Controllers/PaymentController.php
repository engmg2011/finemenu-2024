<?php

namespace App\Http\Controllers;

use App\Constants\PaymentConstants;
use App\Models\Invoice;
use App\Models\Reservation;
use App\Services\PaymentProviders\Hesabe;
use App\Services\PaymentProviders\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{

    public function __construct(private PaymentService $paymentService = new PaymentService())
    {
    }

    public function paymentAvailableInvoice($referenceNumber)
    {
        $invoice = Invoice::where('reference_id', $referenceNumber)->first();
        $invoiceStatus = $invoice->status;
        $reservation = Reservation::where('id', $invoice->reservation_id)->first();
        $reservationStatus = $reservation->status;
        if ($invoiceStatus !== PaymentConstants::INVOICE_PENDING)
            return false;
        if ($reservationStatus === PaymentConstants::RESERVATION_CANCELED)
            return false;
        return true;
    }

    public function hesabeCheckout($referenceNumber)
    {
        $this->paymentService = new PaymentService(new Hesabe());
        // disable multiple payment
        if (!$this->paymentAvailableInvoice($referenceNumber))
            return redirect()->route('invoice.show', $referenceNumber);
        $checkoutLink = $this->paymentService->checkout($referenceNumber);
        if (str_contains($checkoutLink, 'http'))
            return redirect($checkoutLink);
        else
            return $checkoutLink;
    }

    public function hesabeCompleted(Request $request, $referenceNumber)
    {
        $this->paymentService = new PaymentService(new Hesabe());
        return $this->paymentService->completed($request, $referenceNumber);
    }

    public function success(Request $request)
    {
        $data = ["msg" => "Completed Successfully", "color" => "green"];
        $callback = decrypt($request->query('encryptedData'));
        if (isset($callback) && is_array($callback) && $callback['callbackUrl'] !== '') {
            return redirect($callback['callbackUrl'] . '?success=true');
        }
        return view('payment.success', $data);
    }

    public function failed(Request $request)
    {
        $data = ["msg" => "Error: Invalid data received", "color" => "red"];
        if ($request->query('encryptedData')) {
            $callback = decrypt($request->query('encryptedData'));
            if (isset($callback) && is_array($callback) && $callback['callbackUrl'] !== '') {
                return redirect($callback['callbackUrl'] . '?success=false');
            }
        }
        return view('payment.failed', $data);
    }


}
