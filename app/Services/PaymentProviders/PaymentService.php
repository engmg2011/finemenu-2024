<?php

namespace App\Services\PaymentProviders;

class PaymentService
{

    public function __construct(private PaymentProviderInterface $provider)
    {
    }

    public function createLink($referenceNumber): string
    {
        return $this->provider->createLink($referenceNumber);
    }

    public function checkout($referenceNumber)
    {
        return $this->provider->checkout($referenceNumber);
    }

    public function completed($referenceNumber)
    {
        return $this->provider->completed($referenceNumber);
    }

    public function success(): void
    {
        $this->provider->success();
    }

    public function failure(): void
    {
        $this->provider->failure();
    }

    public function createMultipleInvoices()
    {

    }
}
