<?php

namespace App\Services\PaymentProviders;

use App\Constants\AuditServices;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Reservation;
use App\Services\AuditService;

class PaymentService
{

    public function __construct(private PaymentProviderInterface $provider = new Hesabe())
    {
    }

    public function checkout($referenceNumber)
    {
        // TODO :: Check solution without editing Hesabe package
        $callBack = request()->get('CallbackURL', false);
//        if ($callBack)
//            session(['paymentCallback' => $callBack]);
        return $this->provider->checkout($referenceNumber, $callBack);
    }

    public function completed($request, $referenceNumber)
    {
        $process = $this->provider->completed($request, $referenceNumber);
        $reservation = Reservation::whereHas('invoices', function ($query) use ($referenceNumber) {
            $query->where('reference_id', $referenceNumber);
        })->first();

        $invoice = Invoice::where('reference_id', $referenceNumber)->first();
        if($reservation){
            AuditService::log(AuditServices::Reservations,
                $reservation->id,
                " Completed online payment Invoice " . $referenceNumber,
                $invoice->business_id, $invoice->branch_id);

            request()->merge([
                'reservation' => $reservation,
            ]);
        }

        $order = Order::whereHas('invoices', function ($query) use ($referenceNumber) {
            $query->where('reference_id', $referenceNumber);
        })->first();
        if($order){
            AuditService::log(AuditServices::Reservations,
                $order->id,
                " Completed online payment Invoice " . $referenceNumber,
                $invoice->business_id, $invoice->branch_id);
            request()->merge([
                'order' => $order
            ]);
        }
        request()->merge([
           'data' => null,
        ]);

        return $process;
    }

}
