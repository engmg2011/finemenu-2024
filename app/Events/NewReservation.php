<?php

namespace App\Events;

use App\Models\Reservation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewReservation implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Reservation|null $reservation;

    /**
     * Create a new event instance.
     */
    public function __construct($reservationId)
    {
        $this->reservation = Reservation::find($reservationId);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn(): array
    {
        $branchId = $this->reservation->branch_id;
        $businessId = $this->reservation->business_id;
        return [
            new PrivateChannel('business-' . $businessId . '-branch-' . $branchId . '-reservations'),
        ];
    }

    public function broadcastAs()
    {
        return 'new-reservation';
    }
}
