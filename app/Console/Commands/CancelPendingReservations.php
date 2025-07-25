<?php

namespace App\Console\Commands;

use App\Constants\PaymentConstants;
use App\Events\UpdateReservation;
use App\Jobs\SendUpdateReservationNotification;
use App\Models\Invoice;
use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CancelPendingReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cancel-pending-reservations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Code
            $reservationIds = Reservation::where('created_at', '<', now()->subMinutes(5))
                ->where('status', PaymentConstants::RESERVATION_PENDING)
                ->pluck('id');

            Reservation::where('created_at', '<', now()->subMinutes(5))
                ->where('status', PaymentConstants::RESERVATION_PENDING)
                ->update(['status' => PaymentConstants::RESERVATION_CANCELED]);

            Invoice::whereIn('reservation_id', $reservationIds)
                ->where('status', PaymentConstants::INVOICE_PENDING)
                ->update(['status' => PaymentConstants::INVOICE_CANCELED]);

            foreach ($reservationIds as $reservationId) {
                app('App\Repository\Eloquent\ReservationRepository')->setReservationCashedData($reservationId);
                event(new UpdateReservation($reservationId));
                dispatch(new SendUpdateReservationNotification($reservationId, PaymentConstants::RESERVATION_CANCELED));
            }

        } catch (\Exception $e) {
            echo $e->getMessage();
            \Log::error("checking pending reservations -  " . $e->getMessage());
        }

    }
}
