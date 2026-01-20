<?php

namespace App\Jobs;

use App\Constants\PaymentConstants;
use App\Models\Branch;
use App\Models\Business;
use App\Models\Order;
use App\Models\Reservation;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class SendUpdateOrderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Order|null $order;
    public NotificationService $notificationService;

    /**
     * Create a new job instance.
     */
    public function __construct($orderId, public $type = null)
    {
        $this->order = Order::find($orderId);
        // Note: orderable should be Branch
        if($this->order->orderable_type !== get_class(new Branch()))
            $this->notificationService = new NotificationService($this->order->orderable_id);
    }

    public function msg()
    {
        $firstItemName = $this->order->data['reservable']['locales'][0]['name'] ?? "";
        $branchName = $this->order->branch->locales[0]->name ?? "";
        $msg = [];
        $msg['subject'] = $branchName ?? "BarqSolutions";
        $title = $this->type === PaymentConstants::RESERVATION_CANCELED ? "Canceled" : "Updated";
        $msg['message'] = $title . " booking for $firstItemName, Booking ID " . $this->order->id.
            ', ['. Carbon::parse($this->order->from)->format('d-m-y')
            . ' - '. Carbon::parse($this->order->to)->format('d-m-y'). ' ] ,'.
            'Unit '. $this->order->unit . ', Status '. $this->order->status.
            ( $this->order->order_id ? ' 📱': '');
        return $msg;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $business = Business::with('locales')->find($this->order->business_id);
        $adminIds = $this->notificationService->getManagersIds($business);

        $msg = $this->msg();

        $this->notificationService->sendDBNotifications([$this->order->reserved_for_id, ...$adminIds],
            $msg['subject'], $msg['message']);

        try {
            $this->notificationService->sendQrAppOSNotifications($msg['message'], $business, [$this->order->reserved_for_id]);
            $this->notificationService->sendOrdersAppOSNotifications($msg['message'], $business, $adminIds);
        } catch (\Exception $exception) {
            \Log::error(json_encode(["msg" => "Couldn't send notification to multiple devices ",
                "ex" => $exception->getMessage()]));
        }
    }
}
