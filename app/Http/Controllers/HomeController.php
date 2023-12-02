<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\EmailNotification;
use Illuminate\Http\Request;
use Notification;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }


    public function send()
    {

        $user = User::first();

        $project = [
            'greeting' => 'Hi '.$user->name.',',
            'body' => 'This is the project assigned to you.',
            'thanks' => 'Thank you this is from codeanddeploy.com',
            'actionText' => 'View Project',
            'actionURL' => url('/'),
            'id' => 57
        ];

        Notification::send($user, new EmailNotification($project));

        dd('Notification sent!');
    }
}
