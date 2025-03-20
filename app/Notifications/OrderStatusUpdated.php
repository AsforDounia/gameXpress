<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $order;
    protected $oldStatus;

    public function __construct(Order $order ,$oldStatus)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Mise à jour de votre commande #' . $this->order->id)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Le statut de votre commande ' . $this->order->id . ' a été mis à jour.')
            ->line('Ancien statut : ' . $this->oldStatus)
            ->line('Nouveau statut : ' . ucfirst($this->order->status))
            ->line('Merci pour votre confiance !');
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
