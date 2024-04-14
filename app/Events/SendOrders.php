<?php

namespace App\Events;

use App\Actions\OrderAction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendOrders implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $orders;
    public OrderAction $orderAction;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public int $restaurant_id)
    {
        $this->orderAction = app(OrderAction::class);
        request()->request->add(['per-page'=>1000]);
        $this->orders = json_decode(json_encode($this->orderAction->kitchenOrders($restaurant_id)))->data ;
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
