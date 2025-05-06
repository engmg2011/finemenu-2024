<?php

namespace App\Jobs;

use App\Constants\RolesConstants;
use App\Models\Business;
use App\Models\Device;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\OneSignalNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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

        // Notify
        $admins = User::where('business_id', $this->reservation->business_id)
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', [RolesConstants::ADMIN, RolesConstants::SUPERVISOR,
                    RolesConstants::CASHIER, RolesConstants::KITCHEN,
                    RolesConstants::BRANCH_MANAGER, RolesConstants::DRIVER,]);
            })->get();

        if (count($admins)) {

            $config = [
                'app_id' => '59a15ebd-06f5-4da7-9083-c3837d2a66f1',
                'rest_api_key' => 'os_v2_app_lgqv5pig6vg2peedyobx2ktg6gq4bs5iqkwewqnx36rfq2fyosvqb4tbkbhllewkvzrp7fdyoxkgizrmyeviiezsedggvmceo7q2k5a',
                'user_auth_key' => 'os_v2_app_lgqv5pig6vg2peedyobx2ktg6gq4bs5iqkwewqnx36rfq2fyosvqb4tbkbhllewkvzrp7fdyoxkgizrmyeviiezsedggvmceo7q2k5a',
            ];

            // Set config dynamically
            config([
                'onesignal.app_id' => $config['app_id'],
                'onesignal.rest_api_key' => $config['rest_api_key'],
                'onesignal.user_auth_key' => $config['user_auth_key'],
            ]);

            foreach ($admins as $user) {
                $device = Device::where('user_id', $user->id)
                    ->whereNotNull('onesignal_token')
                    ->orderBy('id', 'desc')
                    ->first();
                if ($device) {
                    $firstItemName = $this->reservation->data->reservable->locales[0]?->name ?? "";
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
