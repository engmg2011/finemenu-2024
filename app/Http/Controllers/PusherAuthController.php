<?php

namespace App\Http\Controllers;

use App\Constants\PermissionActions;
use App\Constants\PermissionsConstants;
use App\Constants\PermissionServices;
use Illuminate\Http\Request;
use Pusher\Pusher;
use Pusher\PusherException;

class PusherAuthController extends Controller
{
    /**
     * @throws PusherException
     */
    public function authenticate(Request $request)
    {
        $pusher = new Pusher(env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            ['cluster' => env('PUSHER_APP_CLUSTER'), 'encrypted' => true,]);

        $socketId = $request->input('socket_id');
        $channelName = $request->input('channel_name');

        // Implement your authentication logic here
        // For example, check if the user is authenticated
        $businessNumber = 0;
        if (preg_match('/business-(\d+)-branch-(\d+)/', $channelName, $matches)) {
            $businessNumber = $matches[1];
            $branchNumber = $matches[2];
        }

        $user = $request->user();
        //"private-business-" + this.selectedBusinessId + "-branch-" + this.selectedBranchId + "-orders"
        //"private-business-" + this.selectedBusinessId + "-branch-" + this.selectedBranchId + "-reservations"
        if (
            $user->hasRole('admin', 'web')
            || $user->hasPermissionTo(PermissionsConstants::Branch . '.' . $branchNumber.'.'.PermissionServices::Reservations.'.'.PermissionActions::Read, 'web')
            || $user->hasPermissionTo(PermissionsConstants::Branch . '.' . $branchNumber.'.'.PermissionServices::Orders.'.'.PermissionActions::Read, 'web')
        ) {
            $auth = $pusher->socket_auth($channelName, $socketId);
            return response($auth);
        }
        abort(response()->json("user {$user->id} doesn't have access to business {$businessNumber}" , 400));

    }
}
