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
use Spatie\Permission\Models\Permission;

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

    public function notifyBranchManagers()
    {
        $business = Business::with('locales')->find($this->reservation->business_id);
        $ownerId = $business->user_id;

        $permissionName = PermissionsConstants::Branch . '.' . $this->reservation->branch_id;
        $branchAdmins = User::permission($permissionName)->pluck('id')->toArray();

        $branchPermissions = Permission::where('name', 'like', $permissionName.'.%')->pluck('id');
        $branchManagers = User::whereHas('permissions', function ($q) use ($branchPermissions) {
            $q->whereIn('id', $branchPermissions);
        })->pluck('id')->toArray();

        // AllIds
        $adminIds = array_merge([$ownerId], $branchAdmins, $branchManagers);

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

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $this->notifyBranchManagers();
    }
}
