<?php

namespace App\Events;

use App\Repository\Eloquent\OrderRepository;
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
    public function __construct(public int $business_id)
    {
        $orderRepository = app(OrderRepository::class);
        request()->request->add(['per-page'=>1000]);
        $this->orders = json_decode(json_encode($orderRepository->kitchenOrders($business_id)))->data ;
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
        return 'business-'.$this->business_id.'-orders';
    }
}
