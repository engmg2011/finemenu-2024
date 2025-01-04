<?php
namespace App\Services\PaymentProviders;

interface PaymentProviderInterface{
    public function failed(): string;
    public function checkout($referenceNumber);
    public function completed($request, $referenceNumber);
}
