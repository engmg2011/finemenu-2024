<?php
namespace App\Services\PaymentProviders;

interface PaymentProviderInterface{
    public function checkout($referenceNumber, $callbackUrl);
    public function completed($request, $referenceNumber);
}
