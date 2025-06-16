<?php

namespace App\Notifications;

use App\Repository\ReservationRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReservationNotification extends Notification
{
    use Queueable;

    public $reservation, $firstItemName, $branchName;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected ReservationRepositoryInterface $reservationRepository, private $reservationId)
    {
        $this->reservation = $this->reservationRepository->get($this->reservationId);
        $this->firstItemName = $this->reservable->locales[0]['name'] ?? "";
        $this->branchName = $this->reservation->branch->locales[0]['name'] ?? "";
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [
//            'mail',
            'database'
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Booking")
            ->line("Requested $this->firstItemName from $this->branchName ");
//                    ->action('Notification Action', url('/'))
//                    ->line('Thank you for using our application!');
    }


    public function toDatabase(object $notifiable): array
    {
        return [
            'en' => "Booking $this->firstItemName from $this->branchName ",
            'ar' => "حجز $this->firstItemName من $this->branchName "

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
