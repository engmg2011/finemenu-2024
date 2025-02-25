<?php

namespace App\Events;

use App\Repository\Eloquent\OrderRepository;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
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
    public function __construct(public int $businessId, public int $branchId)
    {
        $orderRepository = app(OrderRepository::class);
        request()->request->add(['per-page'=>1000]);
        $this->orders = json_decode(json_encode($orderRepository->kitchenOrders($businessId)))->data ;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel('business-'.$this->businessId.'-branch-'.$this->branchId.'-orders'),
        ];
    }

    public function broadcastAs()
    {
        return 'business-'.$this->businessId.'-branch-'.$this->branchId.'-orders';
    }
}
