<?php

namespace App\Jobs;

use App\Models\Business;
use App\Models\Reservation;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class SendNewReservationNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Reservation|null $reservation;
    public NotificationService $notificationService;

    /**
     * Create a new job instance.
     */
    public function __construct($reservationId)
    {
        $this->reservation = Reservation::find($reservationId);
        $this->notificationService = new NotificationService($this->reservation);
    }

    public function msg()
    {
        $firstItemName = $this->reservation->data['reservable']['locales'][0]['name'] ?? "";
        $branchName = $this->reservation->branch->locales[0]->name ?? "";
        $msg = [];
        $msg['subject'] = $branchName ?? "MenuAI";
        $msg['message'] = "Booking $firstItemName from $branchName ";
        \Log::debug(json_encode($msg));
        return $msg;
    }

    public function notifyBranchManagers()
    {
        $business = Business::with('locales')->find($this->reservation->business_id);
        $adminIds = $this->notificationService->getManagersIds($business);

        $msg = $this->msg();

        $this->notificationService->sendDBNotifications($adminIds , $msg['subject'], $msg['message']);

        try {
            $this->notificationService->sendBulkOSNotifications($msg, $business, $adminIds);
        } catch (\Exception $exception) {
            \Log::error(json_encode(["msg" => "Couldn't send notification to multiple devices " ,
                "ex" => $exception->getMessage()]));
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->notifyBranchManagers();
    }
}
