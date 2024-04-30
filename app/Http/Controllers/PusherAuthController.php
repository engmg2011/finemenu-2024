<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Exception;
use Pusher\Pusher;

class PusherAuthController extends Controller
{
    public function authenticate(Request $request)
    {
        $pusher = new Pusher( env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            [ 'cluster' => env('PUSHER_APP_CLUSTER'), 'encrypted' => true,]);

        $socketId = $request->input('socket_id');
        $channelName = $request->input('channel_name');

        // Implement your authentication logic here
        // For example, check if the user is authenticated
        $restaurantNumber = 0;
        if (preg_match('/-(\d+)-/', $channelName, $matches)) {
            $restaurantNumber = $matches[1];
        }

        $user = $request->user();
        if(! ($user->hasRole('admin' , 'web') ||
            $user->hasPermissionTo('restaurants.owner.'.$restaurantNumber , 'web'))){
            return throw new Exception("user {$user->id} doesn't have access to restaurant {$restaurantNumber}");
        }

        $auth = $pusher->socket_auth($channelName, $socketId);
        return response($auth);
    }
}
