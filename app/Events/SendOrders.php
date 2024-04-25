<?php

namespace App\Events;

use App\Repository\OrderRepositoryInterface;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendOrders implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $orders;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(OrderRepositoryInterface $orderRepository,
                                public int $restaurant_id)
    {
        request()->request->add(['per-page'=>1000]);
        $this->orders = json_decode(json_encode($orderRepository->kitchenOrders($restaurant_id)))->data ;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel("public_channel");
    }

    public function broadcastAs()
    {
        return 'restaurant-'.$this->restaurant_id.'-orders';
    }
}
