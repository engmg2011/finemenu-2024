<?php

namespace App\Console\Commands;

use App\Constants\PaymentConstants;
use App\Models\Reservation;
use Illuminate\Console\Command;

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
            Reservation::where('created_at', '<', now()->subMinutes(5))
                ->where('status', PaymentConstants::RESERVATION_PENDING)
                ->update(['status' => PaymentConstants::RESERVATION_CANCELED]);
        }catch (\Exception $e){
            echo $e->getMessage();
            \Log::error("checking pending reservations -  " . $e->getMessage());
        }
    }
}
