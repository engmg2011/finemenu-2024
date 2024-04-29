<?php

namespace App\Events;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Log;

class MyEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct($message)
    {
        Log::debug("will dispatch const");
        $this->message = $message;
    }

    public function broadcastOn()
    {
        Log::debug("broad cast on ");
        return ['my-channel'];
    }

    public function broadcastAs()
    {
        Log::debug("broad cast As, ");
        return 'my-event';
    }
}
