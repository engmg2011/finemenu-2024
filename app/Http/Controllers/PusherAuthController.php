<?php

namespace App\Http\Controllers;

use App\Constants\PermissionsConstants;
use App\Constants\RolesConstants;
use Exception;
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

        if (preg_match('/(\d+)/', $channelName, $matches)) {
            $businessNumber = $matches[0];
            $branchNumber = $matches[1];
        }

        $user = $request->user();
        //"private-business-" + this.selectedBusinessId + "-branch-" + this.selectedBranchId + "-orders"
        //"private-business-" + this.selectedBusinessId + "-branch-" + this.selectedBranchId + "-reservations"
        if ($user->hasRole('admin', 'web')
            || $user->hasPermissionTo(PermissionsConstants::Business . "." . $businessNumber, 'web')
            || $user->hasPermissionTo(PermissionsConstants::Branch . '.' . $branchNumber, 'web')
        ) {
            $auth = $pusher->socket_auth($channelName, $socketId);
            return response($auth);
        }
        throw new \Exception("user {$user->id} doesn't have access to business {$businessNumber}");

    }
}
