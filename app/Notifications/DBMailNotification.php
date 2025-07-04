<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DBMailNotification extends Notification
{
    use Queueable;
    /**
     * Create a new notification instance.
     */
    public function __construct(private                                  $subject,
                                private                                  $msg)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [
            //'mail',
            'database'
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->line($this->msg);
//                    ->action('Notification Action', url('/'))
//                    ->line('Thank you for using our application!');
    }


    public function toDatabase(object $notifiable): array
    {
        return [
            'en' => $this->msg,
            'ar' => $this->msg
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
