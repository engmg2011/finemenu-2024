<?php
namespace App\Services\PaymentProviders;

interface PaymentProviderInterface{
    public function checkout($referenceNumber);
    public function completed($request, $referenceNumber);
}
