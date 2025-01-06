<?php

namespace App\Events;

use App\Repository\Eloquent\ReservationRepository;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendReservations implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $reservations;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public int $business_id,public int $branch_id)
    {
        $reservationRepository = app(ReservationRepository::class);
        request()->request->add(['per-page'=>1000]);
        $this->reservations = json_decode(json_encode($reservationRepository->listModel($business_id, $branch_id)))->data ;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel('business-'.$this->business_id.'-branch-'.$this->branch_id.'-reservations'),
        ];
    }

    public function broadcastAs()
    {
        return 'business-'.$this->business_id.'-branch-'.$this->branch_id.'-reservations';
    }
}
