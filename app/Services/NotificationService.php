<?php

namespace App\Services;

use App\Constants\ConfigurationConstants;
use App\Constants\PermissionsConstants;
use App\Models\Device;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\NewReservationNotification;
use Notification;
use Ramsey\Collection\Collection;
use Spatie\Permission\Models\Permission;

class NotificationService
{
    public function __construct(public Reservation $reservation)
    {
    }

    public function getManagersIds($business)
    {
        $ownerId = $business->user_id;

        $permissionName = PermissionsConstants::Branch . '.' . $this->reservation->branch_id;
        $branchAdmins = User::permission($permissionName)->pluck('id')->toArray();

        $branchPermissions = Permission::where('name', 'like', $permissionName . '.%')->pluck('id');
        $branchManagers = User::whereHas('permissions', function ($q) use ($branchPermissions) {
            $q->whereIn('id', $branchPermissions);
        })->pluck('id')->toArray();

        // Merge all admins Ids
        return array_unique(array_merge([$ownerId], $branchAdmins, $branchManagers));
    }

    public function sendDBNotifications(array $userIds, $subject, $msg)
    {
        // DB & Mail Notifications
        $users = User::whereIn('id', $userIds)->get();
        $DBNotification = new NewReservationNotification($subject, $msg);
        Notification::send($users, $DBNotification);
    }

    public function sendBulkOSNotifications($msg, $business, $userIds)
    {
        $devices = new Collection(Device::class);
        foreach ($userIds as $id) {
            $device = Device::whereNotNull('onesignal_token')
                ->where('user_id', $id)->orderBy('last_active', 'desc')->first();
            if ($device)
                $devices->add($device);
        }

        $playerIds = $devices->map(function ($device) {
            return $device->onesignal_token;
        })->toArray();

        // Change config
        config([
            'onesignal.app_id' => $business->getConfig(ConfigurationConstants::ORDERS_ONESIGNAL_APP_ID),
            'onesignal.rest_api_key' => $business->getConfig(ConfigurationConstants::ORDERS_ONESIGNAL_REST_API_KEY),
            'onesignal.user_auth_key' => $business->getConfig(ConfigurationConstants::ORDERS_ONESIGNAL_USER_AUTH_KEY),
        ]);

        if (count($devices)) {
            \OneSignal::sendNotificationToUsers(
                $msg,
                $playerIds
            );
        }

    }
}
