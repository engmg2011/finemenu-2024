<?php

use Carbon\Carbon;

?><!DOCTYPE html>
<html lang="en" dir="ltr">
<body style="background-color:#fff;margin:0;padding:20px;font-family:Arial,sans-serif;color:#000;text-align:left;">
<?php
$reservation = $invoice['reservation'];
$reservable = $invoice['reservation']['data']['reservable'];
$divStyle = "background-color:#f6f6f6;border-radius:10px;padding:15px;margin:5px 20px;font-size: 14px"
?>
<div style="max-width:500px;margin:auto;">
    <h2 style="text-align:center;font-weight:bold;margin:5px 20px;">
        --------------- Electronic Invoice --------------- </h2>
    <div style="text-align:center;margin:20px 20px;">
        <img
            src="https://barcode.tec-it.com/barcode.ashx?data={{ $invoice['reference_id'] }}&code=Code128&translate-esc=false"
            alt="Barcode" style="width:100%;max-height:80px;">
    </div>

    <!-- Booking details -->
    <div style="{{ $divStyle }}">
        <h3 style="margin: 8px 0 10px">Booking details</h3>
        <p style="margin: 8px 0px">
            <span>Booking Number:</span>
            <span style="font-weight:bold;">{{ $invoice['reference_id'] }}</span>
        </p>
        <p style="margin: 8px 0px">
            <span>Status:</span> <span style="font-weight:bold;">{{ $invoice['status'] }}</span>
        </p>
        <p style="margin: 8px 0px">
            <span>From:</span>
            <span
                style="font-weight:bold;">{{ Carbon::parse( $reservation['from'] )->format('Y-m-d') }}</span>
        </p>
        <p style="margin: 8px 0px">
            <span>To:</span>
            <span
                style="font-weight:bold;">{{   Carbon::parse( $reservation['to'] )->format('Y-m-d') }}</span>
        </p>
        <p style="margin: 8px 0px">
            <span>Check-in Time:</span>
            <span
                style="font-weight:bold;">{{ Carbon::parse( $reservation['from'] )->format('g:i A') }}</span>
        </p>
        <p style="margin: 8px 0px">
            <span>Check-out Time:</span>
            <span style="font-weight:bold;">{{ Carbon::parse( $reservation['to'] )->format('g:i A') }}</span>
        </p>
        <p style="margin: 8px 0px">
            <span>Booking Date:</span>
            <span style="font-weight:bold;">{{  $reservation['created_at'] }}</span>
        </p>
        <p style="margin: 8px 0px">
            <span>Booking Time:</span>
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
        @if($reservable['itemable']['address']['en'])
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
</div>
</body>
</html>
