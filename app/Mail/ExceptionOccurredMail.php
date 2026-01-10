<?php


namespace App\Mail;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExceptionOccurredMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $exceptionData;

    public function __construct(array $exceptionData)
    {
        $this->exceptionData = $exceptionData;
    }

    public function build()
    {
        return $this
            ->subject('Exception occurred !')
            ->view('emails.exception')
            ->with($this->exceptionData);
    }
}
