<?php

namespace App\Mail;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestQueuedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $messageText;

    public function __construct(string $messageText)
    {
        $this->messageText = $messageText;
    }

    public function build()
    {
        return $this
            ->subject('✅ Laravel Queued Mail Test')
            ->view('emails.test')
            ->with([
                'messageText' => $this->messageText,
            ]);
    }
}
