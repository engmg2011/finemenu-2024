<?php

use App\Constants\PaymentConstants;
use Carbon\Carbon;

?><!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{__("invoices.Invoice")}} - {{ $invoice['reference_id'] }} </title>
</head>
<body style="background-color:#fff;color:#000;text-align:left;
{{ App::currentLocale() === 'ar' ? 'direction:rtl; text-align:right' : 'direction:ltr; text-align:left' }}">
<div style="{{ $divStyle }}">
    <h2 style="background: #ccc;padding: 8px; font-size:1.2rem; text-transform: uppercase;margin-top:0">
        {{ __("invoices.Booking from") }} {{ ___($invoice->reservation->branch->locales)?->name ?? "" }}
        @if(App::currentLocale() === 'ar' )
            <div class="float-left"><a href="{{ url()->current() }}?lang=en">English</a>  </div>
        @else
            <div class="float-right"><a href="{{ url()->current() }}?lang=ar">عربي</a> </div>
        @endif
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
            {{ __("invoices.Booking") }} {{  ___($reservable['locales'] , App::currentLocale())['name'] ?? "" }} ,  {{ $nights }} {{ __("invoices.nights") }}
            @if($reservation['unit']) , {{ __("invoices.Unit") }} ( {{$reservation['unit']}} ) @endif
        </span>
    </p>
    <p>
        <span> {{ __("invoices.Total Rent") }} </span>:
        <span style="font-weight:bold;">{{ $rentAmount }} {{ __("invoices.KWD") }} </span><br>
    </p>
    <p>
        <span> {{ __("invoices.Insurance") }} </span>:
        <span style="font-weight:bold;">{{ $totalDebit }} {{ __("invoices.KWD") }}</span>
    </p>
    <p>
        <span>{{ __("invoices.Check-in") }}:</span>
        <span
            style="font-weight:bold;">{{ utcToBusinessConverter(Carbon::parse( $reservation['from'] ) , $reservation->business_id)->format('d-m-Y g:i A') }} </span>
    </p>
    <p>
        <span>{{ __("invoices.Check-out") }}:</span>
        <span
            style="font-weight:bold;"> {{ utcToBusinessConverter(Carbon::parse( $reservation['to'] ) , $reservation->business_id)->format('d-m-Y g:i A') }}</span>
    </p>
    <p>
        <span>{{ __("invoices.Booking Date") }}:</span>
        <span
            style="font-weight:bold;">{{ utcToBusinessConverter(Carbon::parse( $reservation['created_at'] ) , $reservation->business_id) }}</span>
    </p>

    @if( isset($reservable['itemable']) &&
         isset($reservable['itemable']['address']) &&
         $reservable['itemable']['address'][App::currentLocale()] ?? false
    )
        <p>
            <span>{{ __("invoices.Address") }}:</span>
            <span style="font-weight:bold;">
                    {{ $reservable['itemable']['address'][App::currentLocale()] ?? "" }}</span>
        </p>
    @endif
    @if( isset($reservable['itemable']['latitude']) )
        <p style="margin-top:10px;">
            <span>{{ __("invoices.Location") }}:</span>
            <a href="{{ "https://www.google.com/maps/@".$reservable['itemable']['latitude'].",".$reservable['itemable']['longitude'].",15z" }}">
                {{ __("invoices.Google Maps Location") }}</a>
        </p>
    @endif

    @foreach ($invoices as $index => $inv)
        @if($inv->id === $invoice->id)
            <h2 style="background: #ccc;padding: 8px; font-size:1.2rem; text-transform: uppercase;margin-top:0">
                {{ __("invoices.CURRENT INVOICE") }}
            </h2>
        @elseif($index === 1)
            <h2 style="background: #ccc;padding: 8px; font-size:1.2rem; text-transform: uppercase;margin-top:0">
                {{ __("invoices.OTHER BOOKING INVOICES") }}
            </h2>
        @else
            <hr/>
        @endif
        <h3 style="margin: 8px 0 10px">{{ __("invoices.Invoice") }} #{{ $inv->id }}
            @if($inv['status'] === PaymentConstants::INVOICE_PAID) <span class="paid">✓</span>
            @else  <span class="unpaid">!</span> @endif
        </h3>
        <p>
            <span>{{ __("invoices.Amount") }}:</span>
            <span style="font-weight:bold;"> {{ $inv['amount'] }} {{ __("invoices.KWD") }}</span>

        </p>
        <p>
            <span>{{ __("invoices.Status") }}:</span>

            @if($inv['status'] === PaymentConstants::INVOICE_PAID)
                <span style="font-weight:bold;">  {{ __("invoices.Paid") }} </span>
            @else
                <span style="font-weight:bold;"> {{ __("invoices.Unpaid") }} </span>
            @endif
        </p>

        @if($inv['paid_at'])
            <p>
                <span>{{ __("invoices.Paid AT") }}:</span>
                <span
                    style="font-weight:bold;">{{ utcToBusinessConverter(Carbon::parse( $inv['paid_at'] ) , $reservation->business_id)   }}</span>
            </p>
        @endif

        <p>
            @if($inv['type'] == 'debit')
                <span>{{ __("invoices.Note") }}:</span>
                <span style="font-weight:bold;"> {{ __("invoices.Refundable after checkout") }}.
            @endif
        </p>

        @if($inv->id === $invoice->id)
            <!-- Other info after current invoice-->
            <p>
                <span>{{ __("invoices.REFERENCE ID") }}:</span>
                <span style="font-weight:bold;" class="reference_number"> ### {{ $invoice['reference_id'] }} ### </span>
            </p>
            <p>
                <span>{{ __("invoices.Reservation Number") }}:</span>
                <span style="font-weight:bold;"># {{ $reservation['id'] ?? "" }} #</span>
            </p>
            <p>
                <span>{{ __("invoices.Customer Name") }}:</span> <span style="font-weight:bold;">
                {{ $reservation['data']['reserved_for']['name'] ?? "" }}</span>
            </p>
            <p>
                <span>{{ __("invoices.Customer Email") }}:</span>
                <span style="font-weight:bold;">
                {{ $reservation['data']['reserved_for']['email'] ?? "" }}</span>
            </p>
            <p>
                <span>{{ __("invoices.Payment note") }}:</span>
                <span style="font-weight:bold;"> {{ $invoice['description'] ?? "" }}</span>
            </p>
        @endif

    @endforeach
</div>
<hr>

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
    .float-left{
        float: left;
    }
    .float-right{
        float: right;
    }
</style>
</body>
</html>
