<?php

namespace App\Notifications;

use App\Repository\OrderRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    use Queueable;

    public $order, $firstItemName, $branchName;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected OrderRepositoryInterface $orderRepository, private $orderId)
    {
        $this->order = $this->orderRepository->get($this->orderId);


        $this->firstItemName = $this->order->orderlines[0]?->data['item']['locales'][0]['name'] ?? "";
        if (count($this->order->orderlines) > 1)
            $this->firstItemName .= " and more ";
        $this->branchName = $this->order->orderable->locales[0]->name ?? "";


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
            'message' => "Requested $this->firstItemName from $this->branchName "
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
