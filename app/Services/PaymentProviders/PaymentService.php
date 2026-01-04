<?php

namespace App\Services\PaymentProviders;

use App\Constants\AuditServices;
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
        })->firstOrFail();
        request()->merge([
           'data' => null,
           'reservation' => $reservation
        ]);
        AuditService::log(AuditServices::Reservations,
            $reservation->id,
            " Completed online payment Invoice " . $referenceNumber,
            $reservation->business_id, $reservation->branch_id);
        return $process;
    }

}
