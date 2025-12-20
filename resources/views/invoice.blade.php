<?php

use App\Constants\PaymentConstants;
use Carbon\Carbon;

?><!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice - {{ $invoice['reference_id'] }} </title>

    <!-- Bootstrap CSS -->
    {{--    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">--}}

    <title>Hello, world!</title>
</head>
<body>
<style>
    html {
        padding: 0 !important;
        margin: 0 !important;
        font-size: 12px;
        text-transform: uppercase;
    }

    body {
        font-family: DejaVu Sans, serif; /* To Accept Arabic */
        direction: ltr;
        text-align: left;
        padding: 0;
        margin: 0 auto;
        /*max-width: 600px;*/
    }

    @media (min-width: 1024px) {
        html {
            font-size: 12px;
        }
    }
</style>
<body style="background-color:#fff;color:#000;text-align:left;">
<?php
$reservation = $invoice['reservation'];
$reservable = $invoice['reservation']['data']['reservable'];
$divStyle = "background-color:#f0f0f0;border-radius:5px;padding:5px;margin:5px 5px;font-size: 1rem";


$invoicesList = $invoice->reservation->invoices;
$invoices = $invoicesList->reject(fn($inv) => $inv->id == $invoice->id)->push($invoice);


$creditInvoices = $invoicesList->filter(fn($inv) => $inv->type == PaymentConstants::INVOICE_CREDIT);
$totalCredit = $creditInvoices->sum('amount');

$debitInvoices = $invoicesList->filter(fn($inv) => $inv->type == PaymentConstants::INVOICE_DEBIT);
$totalDebit = $debitInvoices->sum('amount');

$rentAmount = $totalCredit - $totalDebit;
?>

    <!-- Booking details -->
<div style="{{ $divStyle }}">
    <h2 style="background: #ccc;padding: 8px; font-size:1.2rem; text-transform: uppercase;margin-top:0">
        {{ $invoice->reservation->branch->locales[0]->name }}
        INVOICE
    </h2>
    @php
        $logoSetting = collect($invoice->reservation->business->settings)->firstWhere('key', 'Logo');
        // create base64 image
        if($logoSetting['data'][0]['src']){
            $avatarUrl = $logoSetting['data'][0]['src'];

            // storage_path('app/public/10/5405_Shalehi_icon.png');
            $avatarUrl = str_replace(url('/storage'), "/app/public", $avatarUrl);
            $avatarUrl = storage_path($avatarUrl);
            $arrContextOptions=array(
                "ssl"=>array(
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ),
            );
            $type = pathinfo($avatarUrl, PATHINFO_EXTENSION);
            $avatarData = file_get_contents($avatarUrl, false, stream_context_create($arrContextOptions));
            $avatarBase64Data = base64_encode($avatarData);
            $imageData = 'data:image/' . $type . ';base64,' . $avatarBase64Data;
        }
    @endphp
    @if($logoSetting['data'][0]['src'] ?? false)
        <img id='base64image' src='{{ $imageData }}' alt=""
             style="max-width:100px; max-height: 100px; float: right; margin: 10px"/>
    @endif
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
            style="font-weight:bold;">{{ utcToBusinessConverter(Carbon::parse( $reservation['from'] ) , $reservation->business_id)->format('d-m-Y g:i A') }} </span>
    </p>
    <p style="margin: 8px 0px">
        <span>Check-out:</span>
        <span
            style="font-weight:bold;"> {{ utcToBusinessConverter(Carbon::parse( $reservation['to'] ) , $reservation->business_id)->format('d-m-Y g:i A') }}</span>
    </p>
    <p style="margin: 8px 0px">
        <span>Booking Date:</span>
        <span
            style="font-weight:bold;">{{ utcToBusinessConverter(Carbon::parse( $reservation['created_at'] ) , $reservation->business_id) }}</span>
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
    @if(isset($reservable['itemable']) && isset($reservable['itemable']['address']) && $reservable['itemable']['address']['en'] ?? false )
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
<div style="{{ $divStyle }}">

    <h2 style="background: #ccc;padding: 8px; font-size:1.2rem; text-transform: uppercase;margin-top:0">
        INVOICES LIST
    </h2>
    <p>
        <b> Rent </b>: {{ $rentAmount }} KWD<br>
    </p>
    <p>
        <b> Insurance </b>: {{ $totalDebit }} KWD
    </p>

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
            <span style="font-weight:bold;"> {{ $inv['amount'] }} KWD</span>

        </p>
        <p>
            <span>Status:</span>
            <span
                style="font-weight:bold;"> {{ $inv['status'] === PaymentConstants::INVOICE_PAID ? 'Paid' : 'Unpaid' }}</span>
        </p>

        @if($invoice['paid_at'])
            <p>
                <span>Paid AT:</span>
                <span
                    style="font-weight:bold;">{{ utcToBusinessConverter(Carbon::parse( $invoice['paid_at'] ) , $reservation->business_id)   }}</span>
            </p>
        @endif

        <p>
            @if($inv['type'] == 'debit')
                <span>Note:</span>
                <span style="font-weight:bold;"> Refundable after checkout.
            @endif
        </p>
    @endforeach
</div>
</body>
</html>
