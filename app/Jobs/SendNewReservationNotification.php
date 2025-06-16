<?php

namespace App\Jobs;

use App\Constants\ConfigurationConstants;
use App\Constants\PermissionsConstants;
use App\Constants\RolesConstants;
use App\Models\Business;
use App\Models\Device;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Notifications\NewReservationNotification;
use App\Notifications\OneSignalNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Notification;
use Ramsey\Collection\Collection;

class SendNewReservationNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Reservation|null $reservation;
    /**
     * Create a new job instance.
     */
    public function __construct($reservationId)
    {
        $this->reservation = Reservation::find($reservationId);
    }

    public function notifyAdmins()
    {
        $business = Business::with('locales')->find($this->reservation->business_id);

        $this->notifyBusinessOwner($business);


        $permissionName = PermissionsConstants::Branch . '.' . $this->reservation->branch_id;
        $adminIds = User::permission($permissionName)->pluck('id');


        // DB & Mail Notifications
        $users = User::whereIn('id', $adminIds)->get();
        $DBNotification = app()->makeWith(NewReservationNotification::class, ['reservationId' => $this->reservation->id]);
        Notification::send($users, $DBNotification);



        $devices = new Collection(Device::class);
        foreach ($adminIds as $id) {
            $device = Device::whereNotNull('onesignal_token')
                ->where('user_id', $id)->orderBy('last_active', 'desc')->first();
            if ($device)
                $devices->add($device);
        }



        if (count($devices)) {
            // Set config dynamically
            config([
                'onesignal.app_id' => $business->getConfig(ConfigurationConstants::ORDERS_ONESIGNAL_APP_ID),
                'onesignal.rest_api_key' => $business->getConfig(ConfigurationConstants::ORDERS_ONESIGNAL_REST_API_KEY),
                'onesignal.user_auth_key' => $business->getConfig(ConfigurationConstants::ORDERS_ONESIGNAL_USER_AUTH_KEY),
            ]);

            foreach ($devices as $device) {
                $firstItemName = $this->order->orderlines[0]?->data['item']['locales'][0]['name'] ?? "";
                $branchName = $this->reservation->branch->locales[0]->name ?? "";
                try {
                    $subject = $business->locales[0]?->name ?? 'MenuAI';
                    $msg = "Booking $firstItemName from $branchName ";
                    $device->notify(new OneSignalNotification($subject, $msg));
                } catch (\Exception $exception) {
                    \Log::error(json_encode(["msg" => "Couldn't send notification to device id " . $device->id,
                        "ex" => $exception->getMessage()]));
                }
            }
        }


    }

    private function notifyBusinessOwner($business)
    {
        // send to business owner & branch admins
        $userId = $business->user_id;
        $device = Device::where('user_id', $userId)
            ->whereNotNull('onesignal_token')
            ->orderBy('id', 'desc')
            ->first();
        if ($device) {
            $firstItemName = $this->reservation->data->item->locales[0]?->name ?? "";
            $branchName = $this->reservation->branch->locales[0]->name ?? "";
            try {
                $device->notify(new OneSignalNotification('MenuAI', "Booking $firstItemName from $branchName "));
            } catch (\Exception $exception) {
                \Log::error(json_encode(["msg" => "Couldn't send notification to device id " . $device->id,
                    "ex" => $exception->getMessage()]));
            }
        }

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $this->notifyAdmins();
    }
}
