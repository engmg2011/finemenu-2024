<?php

namespace App\Events;

use App\Repository\Eloquent\OrderRepository;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
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
        $orderRepository = app(OrderRepository::class);
        $this->order = $orderRepository->get($orderId);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('restaurant-'.$this->order->orderable_id.'-orders'),
        ];
    }

//    public function broadcastOn()
//    {
//        return new Channel("public_channel");
//    }

    public function broadcastAs()
    {
        return 'new-order';
    }
}
