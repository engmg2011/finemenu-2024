<?php

namespace App\Services;

use App\Constants\ConfigurationConstants;
use App\Constants\PermissionsConstants;
use App\Models\Device;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\DBMailNotification;
use Berkayk\OneSignal\OneSignalClient;
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
        $DBNotification = new DBMailNotification($subject, $msg);
        Notification::send($users, $DBNotification);
    }

    public function sendQrAppOSNotifications($msg, $business, $userIds)
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


        if (count($devices)) {
            $oneSignal = new OneSignalClient(
                $business->getConfig(ConfigurationConstants::QR_ONESIGNAL_APP_ID),
                $business->getConfig(ConfigurationConstants::QR_ONESIGNAL_REST_API_KEY),
                ''
            );

            $oneSignal->sendNotificationToUser(
                $msg,
                $playerIds,
                $url = null,
                $data = null,
                $buttons = null,
                $schedule = null,
                $headings = "",
                $subtitle = null
            );
        }

    }
    public function sendOrdersAppOSNotifications($msg, $business, $userIds)
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

        if (count($devices)) {
            // Orders config
            $oneSignal = new OneSignalClient(
                $business->getConfig(ConfigurationConstants::ORDERS_ONESIGNAL_APP_ID),
                $business->getConfig(ConfigurationConstants::ORDERS_ONESIGNAL_REST_API_KEY),
                ''
            );

            $oneSignal->sendNotificationToUser(
                $msg,
                $playerIds,
                $url = null,
                $data = null,
                $buttons = null,
                $schedule = null,
                $headings = "",
                $subtitle = null
            );
        }

    }
}
