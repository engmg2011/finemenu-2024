<?php
namespace App\Services\PaymentProviders;

use Request;

interface PaymentProviderInterface{
    public function checkout($referenceNumber, $callbackUrl);
    public function completed($request, $referenceNumber);
    public function hesabeWebhookCompleted(Request $request, string $referenceId);
}
