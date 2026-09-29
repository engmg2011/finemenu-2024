<?php

namespace App\Http\Controllers;

use App\Constants\PaymentConstants;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Reservation;
use App\Repository\CouponRepositoryInterface;
use App\Services\PaymentProviders\Hesabe;
use App\Services\PaymentProviders\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{

    public function __construct(
        private PaymentService $paymentService = new PaymentService(),
        private ?CouponRepositoryInterface $couponRepository = null
    ) {
        if ($this->couponRepository === null) {
            $this->couponRepository = app(CouponRepositoryInterface::class);
        }
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
        // Make sure the order's coupon code is still reserved before the customer pays
        $invoice = Invoice::where('reference_id', $referenceNumber)->first();
        if ($invoice->order_id && !$this->couponRepository->holdForPayment($invoice->order_id)) {
            return view('payment.failed', [
                'msg'   => 'The coupon code on this order is no longer available. Please place a new order.',
                'color' => 'red',
            ]);
        }
        $checkoutLink = $this->paymentService->checkout($referenceNumber);
        if (str_contains($checkoutLink, 'http'))
            return redirect($checkoutLink);
        else
            return $checkoutLink;
    }

    public function hesabeCompleted(Request $request, $referenceNumber)
    {
        $this->paymentService = new PaymentService(new Hesabe());
        // Coupon redemption is confirmed by the Order model when the order is marked paid.
        return $this->paymentService->completed($request, $referenceNumber);
    }

    public function hesabeWebhookCompleted(Request $request, $referenceNumber)
    {
        $this->paymentService = new PaymentService(new Hesabe());
        // Coupon redemption is confirmed by the Order model when the order is marked paid.
        return $this->paymentService->hesabeWebhookCompleted($request, $referenceNumber);
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
