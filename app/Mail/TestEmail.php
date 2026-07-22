<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->from('info@barq.solutions', 'Barq Solutions')
            ->to('eng.mg2011@gmail.com')
            ->subject('Hello from Barq Solutions 2')
            ->text('emails.test-plain') // For plain text email
            ->with([
                'message' => 'This is a test email sent via Laravel.',
            ]);
    }
}