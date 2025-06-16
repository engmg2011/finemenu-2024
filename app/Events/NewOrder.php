<?php

namespace App\Events;

use App\Constants\PermissionsConstants;
use App\Models\Branch;
use App\Models\Device;
use App\Models\Order;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Notifications\OneSignalNotification;
use App\Repository\Eloquent\OrderRepository;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Notification;
use Ramsey\Collection\Collection;

// Broadcasting by Pusher
class NewOrder implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order, $branchId;

    /**
     * Create a new event instance.
     */
    public function __construct($orderId)
    {
        $this->order = Order::with(OrderRepository::Relations)->find($orderId);

        $this->branchId = $this->order->orderable_id;
        $this->sendPushNotificationToAdmins();
    }

    public function sendPushNotificationToAdmins()
    {
        // Get all branch managers Ids
        // send to latest device of everyone of them

        $permissionName = PermissionsConstants::Branch . '.' . $this->branchId;
        try {
            $adminIds = User::permission($permissionName)->pluck('id');
            $devices = new Collection(Device::class);
            foreach ($adminIds as $id) {
                $device = Device::whereNotNull('onesignal_token')
                    ->where('user_id', $id)->orderBy('last_active', 'desc')->first();
                if ($device)
                    $devices->add($device);
            }

            $firstItemName = $this->order->orderlines[0]?->data['item']['locales'][0]['name'] ?? "";
            if (count($this->order->orderlines) > 1)
                $firstItemName .= " and more ";
            $branchName = $this->order->orderable->locales[0]->name ?? "";

            // DB & Mail Notifications
            $users = User::whereIn('id', $adminIds)->get();
            $DBNotification = app()->makeWith(NewOrderNotification::class, ['orderId' => $this->order->id]);
            Notification::send($users, $DBNotification);


            // OneSignal
            if (count($devices)) {
                foreach ($devices as $device) {
                    $device->notify(new OneSignalNotification('MenuAI', "Requested $firstItemName from $branchName "));
                }
            }

        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn(): array
    {
        $businessId = Branch::find($this->branchId)->business_id;
        return [
            new PrivateChannel('business-' . $businessId . '-branch-' . $this->branchId . '-orders'),
        ];
    }

    public function broadcastAs()
    {
        return 'new-order';
    }
}
