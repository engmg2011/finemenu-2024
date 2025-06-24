<?php

namespace App\Jobs;

use App\Constants\PaymentConstants;
use App\Models\Business;
use App\Models\Reservation;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class SendUpdateReservationNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Reservation|null $reservation;
    public NotificationService $notificationService;

    /**
     * Create a new job instance.
     */
    public function __construct($reservationId, public $type = null)
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
        $title = $this->type === PaymentConstants::RESERVATION_CANCELED ? "Canceled" : "Updated";
        $msg['message'] = $title . " booking for $firstItemName from $branchName Booking ID " . $this->reservation->id;
        return $msg;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $business = Business::with('locales')->find($this->reservation->business_id);
        $adminIds = $this->notificationService->getManagersIds($business);

        $msg = $this->msg();

        $this->notificationService->sendDBNotifications([$this->reservation->reserved_for_id, ...$adminIds],
            $msg['subject'], $msg['message']);

        try {
            $this->notificationService->sendQrAppOSNotifications($msg, $business, [$this->reservation->reserved_for_id]);
            $this->notificationService->sendOrdersAppOSNotifications($msg, $business, $adminIds);
        } catch (\Exception $exception) {
            \Log::error(json_encode(["msg" => "Couldn't send notification to multiple devices ",
                "ex" => $exception->getMessage()]));
        }
    }
}
