<?php

use Carbon\Carbon;

?><!DOCTYPE html>
<html lang="en" dir="ltr" style="margin: 0; padding: 0">
<style>
    html {
        padding: 0 !important;
        margin: 0 !important;
        font-size: 8px;
        text-transform: uppercase;
    }
    body {
        font-family: DejaVu Sans, sans-serif;
        padding: 0;
        width: 100%;
        max-width: 500px;
        margin: 0 auto;
    }
    @media (min-width: 1024px){
        html { font-size: 12px;}
    }
</style>
<body style="background-color:#fff;color:#000;text-align:left;">
<?php
$reservation = $invoice['reservation'];
$reservable = $invoice['reservation']['data']['reservable'];
$divStyle = "background-color:#f0f0f0;border-radius:5px;padding:5px;margin:5px 5px;font-size: 1rem"
?>
    <!-- Booking details -->
<div style="{{ $divStyle }}">
    <h2 style="background: #ccc;padding: 8px; font-size:1.2rem; text-transform: uppercase;margin-top:0">
        {{ $invoice->reservation->branch->locales[0]->name }}
        INVOICE
    </h2>
{{--    <img
        src="https://barcode.tec-it.com/barcode.ashx?data={{ $invoice['reference_id'] }}&code=Code128&translate-esc=false"
        alt="" style="width:100%;max-height:80px;">--}}
    <h3 style="margin: 8px 0 10px">Booking details</h3>
    <p style="margin: 8px 0px">
        <span>Booking :</span>
        <span style="font-weight:bold;">#{{ $invoice['reference_id'] }}</span>
    </p>
    <p style="margin: 8px 0px">
        <span>Status:</span> <span style="font-weight:bold;">{{ $invoice['status'] }}</span>
    </p>
    <?php
    $checkIn = Carbon::parse($reservation['from']);
    $checkOut = Carbon::parse($reservation['to']);
    $nights = $checkIn->copy()->startOfDay()->diffInDays($checkOut->copy()->startOfDay());
    ?>
    <p style="margin: 8px 0px">
        <span>Details:</span>
        <span
            style="font-weight:bold;">Chalet {{  $reservable['locales'][0]['name'] ?? "" }}, {{ $nights }} nights</span>
    </p>
    <p style="margin: 8px 0px">
        <span>Check-in:</span>
        <span
            style="font-weight:bold;">{{ Carbon::parse( $reservation['from'] )->format('d-m-Y g:i A') }} </span>
    </p>
    <p style="margin: 8px 0px">
        <span>Check-out:</span>
        <span
            style="font-weight:bold;"> {{ Carbon::parse( $reservation['to'] )->format('d-m-Y g:i A') }}</span>
    </p>
    <p style="margin: 8px 0px">
        <span>Booking Date:</span>
        <span style="font-weight:bold;">{{  $reservation['created_at'] }}</span>
    </p>
</div>

<!-- Chalet info -->
<div style="{{ $divStyle }}">
    <h3 style="margin: 8px 0 10px">Chalet details</h3>
    <p style="margin: 8px 0px">
        <span>Chalet Name:</span>
        <span
            style="font-weight:bold;">{{  $reservable['locales'][0]['name'] ?? "" }}</span>
    </p>
    @if($reservation['unit'])
        <p>
            <span>Unit:</span>
            <span style="font-weight:bold;">{{$reservation['unit']}}</span>
        </p>
    @endif
    @if(isset($reservable['itemable']) && $reservable['itemable']['address']['en'])
        <p>
            <span>Address:</span>
            <span style="font-weight:bold;">
                    {{ $reservable['itemable']['address']['en'] ?? "" }}</span>
        </p>
    @endif
    @if( isset($reservable['itemable']['latitude']) )
        <p style="margin-top:10px;">
            <span>Location:</span>
            <a href="{{ "https://www.google.com/maps/@".$reservable['itemable']['latitude'].",".$reservable['itemable']['longitude'].",15z" }}">
                Google Maps Location</a>
        </p>
    @endif
</div>

<!-- Customer & payment info -->
<div style="{{ $divStyle }}">
    <h3 style="margin: 8px 0 10px">Customer details</h3>
    <p style="margin: 8px 0px">
        <span>Name:</span> <span style="font-weight:bold;">
                {{ $reservation['data']['reserved_for']['name'] ?? "" }}</span>
    </p>
    <p style="margin: 8px 0px">
        <span>Email:</span>
        <span style="font-weight:bold;">
                {{ $reservation['data']['reserved_for']['email'] ?? "" }}</span>
    </p>
    <p style="margin: 8px 0px">
        <span>Booking Unit:</span>
        <span style="font-weight:bold;">{{ $reservation['unit'] ?? "" }}</span>
    </p>
</div>
<?php
$invoicesList = $invoice->reservation->invoices;
$invoices = $invoicesList->reject(fn($inv) => $inv->id == $invoice->id)->push($invoice);
?>
<div style="{{ $divStyle }}">
    @foreach ($invoices as $index => $inv)
        @if($index !== 0)
            <hr/>
        @endif
        <h3 style="margin: 8px 0 10px"># Invoice {{ $inv->id }}
            @if($inv->id === $invoice->id)
                <span style="font-size: .8em; line-height: 1rem; color:#666">
                     ( Current invoice )
                </span>
            @endif
        </h3>
        <p>
            <span>Type:</span>
            <span style="font-weight:bold;"> {{ ucfirst($inv['type']) }} </span>
        </p>
        <p>
            <span>Amount:</span>
            <span style="font-weight:bold;"> {{ $inv['amount'] }} KD</span>

        </p>
        <p>
            <span>Status:</span>
            <span
                style="font-weight:bold;"> {{ $inv['status'] === \App\Constants\PaymentConstants::INVOICE_PAID ? 'Paid' : 'Unpaid' }}</span>
        </p>

        @if($invoice['paid_at'])
            <p  >
                <span>Paid AT:</span>
                <span style="font-weight:bold;">{{ Carbon::parse( $invoice['paid_at'] )->format('d-m-Y g:i A') }}</span>
            </p>
        @endif

        <p>
            @if($inv['type'] == 'debit')
                <span>Note:</span>
                <span style="font-weight:bold;"> Refundable after checkout.
                @else
                        <span>Note:</span>
                        <span style="font-weight:bold;"> Non-refundable confirmation.
            @endif
        </p>
    @endforeach
</div>
</body>
</html>
