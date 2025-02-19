<?php

namespace Database\Seeders;

use App\Constants\PaymentConstants;
use App\Models\Reservation;
use Illuminate\Database\Seeder;

class ReservationDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ['prices','media','locales']
        Reservation::with('invoices',
            'reservable.locales',
            'reservable.media',
            'reservedFor',
            'reservedBy'
        )->each(function ($reservation) {

            $price = 0;
            foreach ($reservation->invoices as $invoice) {
                if ($invoice->type === PaymentConstants::INVOICE_CREDIT)
                    $price += $invoice->amount;
                if ($invoice->type === PaymentConstants::INVOICE_DEBIT)
                    $price -= $invoice->amount;
            }

            $cachedData = [];
            $cachedData += [
                "reservable" => $reservation->reservable,
                "reserved_for" => $reservation->reservedFor,
                "reserved_by" => $reservation->reservedBy,
                "invoices" => $reservation->invoices,
                "subtotal_price" => $price,
                "total_price" => $price
            ];

            $reservation->update(['data' => $cachedData]);
        });
    }
}
