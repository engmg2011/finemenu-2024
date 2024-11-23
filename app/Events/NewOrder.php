<?php

namespace App\Events;

use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OneSignalNotification;
use App\Repository\Eloquent\OrderRepository;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOrder implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;

    /**
     * Create a new event instance.
     */
    public function __construct($orderId)
    {
        $this->order = Order::with(OrderRepository::Relations)->find($orderId);
        $this->notifyAdmins();
    }

    public function notifyAdmins()
    {
        // send to business owner & branch admins
        if($this->order->orderableType === Branch::class) {
            $userId = Branch::select('user_id')->find($this->order->orderable_id)?->user_id;
            $userDevices = User::find($userId)->devices;
            foreach ($userDevices as $device) {
                if($device->onesignal_token)
                {
                    $firstItemName =  $this->order->orderlines[0]->data->item->locales[0]?->name ?? "";
                    if(count($this->order->orderlines) > 1)
                        $firstItemName .= " and more ";
                    $branchName = $this->order->orderable->locales[0]->name ?? "";
                    $device->notify(new OneSignalNotification('MenuAI', "Requested $firstItemName from $branchName "));

                }
            }
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn(): array
    {
        $branchId = $this->order->orderable_id ;
        $businessId = Branch::find($branchId)->business_id;
        return [
            new PrivateChannel('business-'.$businessId.'-branch-'.$branchId.'-orders'),
        ];
    }

    public function broadcastAs()
    {
        return 'new-order';
    }
}
