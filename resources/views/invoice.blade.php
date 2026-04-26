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
</head>
<body style="background-color:#fff;color:#000;text-align:left;">
<?php
$reservation = $invoice['reservation'];
$reservable = $invoice['reservation']['data']['reservable'];
$divStyle = "background-color:#f0f0f0;border-radius:5px;padding:5px;margin:5px 5px;font-size: 1rem";


$invoicesList = $invoice->reservation->invoices;
$invoices = $invoicesList->reject(fn($inv) => $inv->id == $invoice->id)->prepend($invoice);


$creditInvoices = $invoicesList->filter(fn($inv) => $inv->type == PaymentConstants::INVOICE_CREDIT);
$totalCredit = $creditInvoices->sum('amount');

$debitInvoices = $invoicesList->filter(fn($inv) => $inv->type == PaymentConstants::INVOICE_DEBIT);
$totalDebit = $debitInvoices->sum('amount');

$rentAmount = $totalCredit - $totalDebit;
$logoSetting =isset($invoice->reservation->business->settings) ?
 collect($invoice->reservation->business->settings)->firstWhere('key', 'Logo') : [];
// create base64 image
if(isset($logoSetting['data']) && $logoSetting['data'][0]['src']){
    $avatarUrl = $logoSetting['data'][0]['src'];
    $storageUrl = str_replace("http://", "https://", url('/storage'));
    // storage_path('app/public/10/5405_Shalehi_icon.png');
    $avatarUrl = str_replace($storageUrl, "/app/public", $avatarUrl);
    $avatarUrl = storage_path($avatarUrl);
    $arrContextOptions=array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );
    $type = pathinfo($avatarUrl, PATHINFO_EXTENSION);
    $imageData = null;
    try{
        $avatarData = file_get_contents($avatarUrl, false, stream_context_create($arrContextOptions));
        $avatarBase64Data = base64_encode($avatarData);
        $imageData = 'data:image/' . $type . ';base64,' . $avatarBase64Data;
    }catch (Exception $e){
        \Log::error("Can't get content for : ". $avatarUrl);
    }
}
?>
<div style="{{ $divStyle }}">
    <h2 style="background: #ccc;padding: 8px; font-size:1.2rem; text-transform: uppercase;margin-top:0">
        {{ $invoice->reservation->branch->locales[0]->name ?? "" }}
        BOOKING
    </h2>
    @if(isset($logoSetting['data']) && $logoSetting['data'][0]['src'] ?? false && $imageData != null)
        <img id='base64image' src='{{ $imageData }}' alt="" style="max-width:100px; max-height: 100px; float: right; margin: 10px"/>
    @endif

    <?php
    $checkIn = Carbon::parse($reservation['from']);
    $checkOut = Carbon::parse($reservation['to']);
    $nights = $checkIn->copy()->startOfDay()->diffInDays($checkOut->copy()->startOfDay());
    ?>
    <p>
        <span
            style="font-weight:bold;">
            Booking {{  $reservable['locales'][0]['name'] ?? "" }} , {{ $nights }} nights
            @if($reservation['unit']) , Unit ( {{$reservation['unit']}} ) @endif
        </span>
    </p>
    <p>
        <span> Total Rent </span>:
        <span style="font-weight:bold;">{{ $rentAmount }} KWD </span><br>
    </p>
    <p>
        <span> Insurance </span>:
        <span style="font-weight:bold;">{{ $totalDebit }} KWD</span>
    </p>
    <p>
        <span>Check-in:</span>
        <span
            style="font-weight:bold;">{{ utcToBusinessConverter(Carbon::parse( $reservation['from'] ) , $reservation->business_id)->format('d-m-Y g:i A') }} </span>
    </p>
    <p>
        <span>Check-out:</span>
        <span
            style="font-weight:bold;"> {{ utcToBusinessConverter(Carbon::parse( $reservation['to'] ) , $reservation->business_id)->format('d-m-Y g:i A') }}</span>
    </p>
    <p>
        <span>Booking Date:</span>
        <span
            style="font-weight:bold;">{{ utcToBusinessConverter(Carbon::parse( $reservation['created_at'] ) , $reservation->business_id) }}</span>
    </p>

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

    @foreach ($invoices as $index => $inv)
        @if($inv->id === $invoice->id)
            <h2 style="background: #ccc;padding: 8px; font-size:1.2rem; text-transform: uppercase;margin-top:0">
                CURRENT INVOICE
            </h2>
        @elseif($index === 1)
            <h2 style="background: #ccc;padding: 8px; font-size:1.2rem; text-transform: uppercase;margin-top:0">
                OTHER BOOKING INVOICES
            </h2>
        @else
            <hr/>
        @endif
        <h3 style="margin: 8px 0 10px">Invoice #{{ $inv->id }}
            @if($inv['status'] === PaymentConstants::INVOICE_PAID) <span class="paid">✓</span>
            @else  <span class="unpaid">!</span> @endif
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

            @if($inv['status'] === PaymentConstants::INVOICE_PAID)
                <span style="font-weight:bold;">  Paid </span>
            @else
                <span style="font-weight:bold;"> Unpaid </span>
            @endif
        </p>

        @if($invoice['paid_at'])
            <p>
                <span>Paid AT:</span>
                <span
                    style="font-weight:bold;">{{ utcToBusinessConverter(Carbon::parse( $inv['paid_at'] ) , $reservation->business_id)   }}</span>
            </p>
        @endif

        <p>
            @if($inv['type'] == 'debit')
                <span>Note:</span>
                <span style="font-weight:bold;"> Refundable after checkout.
            @endif
        </p>

        @if($inv->id === $invoice->id)
            <!-- Other info after current invoice-->
            <p>
                <span>REFERENCE ID:</span>
                <span style="font-weight:bold;">### {{ $invoice['reference_id'] }} ### </span>
            </p>
            <p>
                <span>Customer Name:</span> <span style="font-weight:bold;">
            {{ $reservation['data']['reserved_for']['name'] ?? "" }}</span>
            </p>
            <p>
                <span>Customer Email:</span>
                <span style="font-weight:bold;">
            {{ $reservation['data']['reserved_for']['email'] ?? "" }}</span>
            </p>
        @endif

    @endforeach
</div>


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
    p, h2, h3{
        padding: 0 8px;
    }

    @media (min-width: 1024px) {
        html {
            font-size: 12px;
        }
    }
    .paid,.unpaid{
        width: 10px;
        height: 10px;
        color: white;
        border-radius: 100%;
        padding: 3px;
        margin-left: 8px;
        display: inline-flex;
        justify-content: center;
        align-items: center;
    }
    .paid{
        background: green;
    }
    .unpaid{
        background: grey;
    }


</style>
</body>
</html>
