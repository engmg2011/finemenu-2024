<?php
namespace App\Services\PaymentProviders;

interface PaymentProviderInterface{
    public function createLink($referenceNumber): string;
    public function checkPayment(): void;
    public function success(): void;
    public function failure(): void;
    public function checkout($referenceNumber);
    public function completed($referenceNumber);
}
