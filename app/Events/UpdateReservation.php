<?php

namespace App\Events;

use App\Models\Business;
use App\Models\Device;
use App\Models\Reservation;
use App\Notifications\OneSignalNotification;
use App\Repository\Eloquent\ReservationRepository;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateReservation implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Reservation|null $reservation;

    /**
     * Create a new event instance.
     */
    public function __construct($reservationId)
    {
        $this->reservation = Reservation::with(ReservationRepository::Relations)->find($reservationId);
        $this->notifyAdmins();
    }

    public function notifyAdmins()
    {
        // send to business owner & branch admins
        $userId = Business::select('user_id')->find($this->reservation->business_id)?->user_id;

        $device = Device::where('user_id', $userId)->orderBy('id', 'desc')->first();
        if ($device && $device->onesignal_token) {
            $firstItemName = $this->reservation->data->item->locales[0]?->name ?? "";
            $branchName = $this->reservation->branch->locales[0]->name ?? "";
            try {
                $device->notify(new OneSignalNotification('MenuAI', "Updated Booking $firstItemName from $branchName "));
            } catch (\Exception $exception) {
                \Log::error(json_encode(["msg" => "Couldn't send notification to device id " . $device->id,
                    "ex" => $exception->getMessage()]));
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
        $branchId = $this->reservation->branch_id;
        $businessId = $this->reservation->business_id;
        return [
            new PrivateChannel('business-' . $businessId . '-branch-' . $branchId . '-reservations'),
        ];
    }

    public function broadcastAs()
    {
        return 'update-reservation';
    }
}
