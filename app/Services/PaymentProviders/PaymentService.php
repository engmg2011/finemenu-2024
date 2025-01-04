<?php

namespace App\Services\PaymentProviders;

class PaymentService
{

    public function __construct(private PaymentProviderInterface $provider)
    {
    }

    public function checkout($referenceNumber)
    {
        return $this->provider->checkout($referenceNumber);
    }

    public function completed($request, $referenceNumber)
    {
        return $this->provider->completed($request, $referenceNumber);
    }

    public function failed(): void
    {
        $this->provider->failed();
    }
}
