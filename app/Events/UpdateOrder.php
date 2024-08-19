<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateOrder implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;

    /**
     * Create a new event instance.
     */
    public function __construct($orderId)
    {
        $this->order = Order::with([  'orderLines.locales', 'locales',
            'orderLines.prices', 'orderLines.item.locales' ])->find($orderId);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('business-'.$this->order->orderable_id.'-orders'),
        ];
    }

    public function broadcastAs()
    {
        return 'update-order';
    }
}
