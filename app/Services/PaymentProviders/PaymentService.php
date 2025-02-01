<?php

namespace App\Services\PaymentProviders;

class PaymentService
{

    public function __construct(private PaymentProviderInterface $provider = new Hesabe())
    {
    }

    public function checkout($referenceNumber)
    {
        // TODO :: Check solution without editing Hesabe package
        $callBack = request()->get('CallbackURL', false);
        if($callBack)
            session(['paymentCallback' => $callBack]);
        return $this->provider->checkout($referenceNumber);
    }

    public function completed($request, $referenceNumber)
    {
        return $this->provider->completed($request, $referenceNumber);
    }

}
