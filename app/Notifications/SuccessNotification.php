<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuccessNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */

    protected $order;

    public function __construct($order)
    {
        $this->order = $order;
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
    public function toMail($notifiable)
    {
        if ($notifiable->hasRole('super_admin') || $notifiable->hasRole('manager')) {
            return $this->adminEmail();
        }
        return $this->customerEmail();
    }

    /**
     * Email pour l'Administrateur.
     */
    private function adminEmail()
    {
        return (new MailMessage)
            ->subject('🛍 Nouveau paiement reçu - Commande #' . $this->order->id)
            ->greeting('Bonjour Administrateur,')
            ->line('Un nouveau paiement a été effectué avec succès pour la commande #' . $this->order->id . '.')
            ->line('Client : ' . $this->order->user->name)
            ->line('Total de la commande : ' . $this->order->total_price . '€')
            ->action('Voir la commande', url('http://127.0.0.1:8000/api/v3/orders/' . $this->order->id))
            ->line('Veuillez traiter cette commande dès que possible.');
    }

    /**
     * Email pour le Client.
     */
    private function customerEmail()
    {
        return (new MailMessage)
            ->subject('✅ Paiement réussi - Commande #' . $this->order->id)
            ->greeting('Bonjour ' . $this->order->user->name . ',')
            ->line('Nous avons bien reçu votre paiement pour la commande #' . $this->order->id . '.')
            ->line('Total de la commande : ' . $this->order->total_price . '€')
            ->action('Voir ma commande', url('http://127.0.0.1:8000/api/v3/orders/' . $this->order->id))
            ->line('Merci pour votre achat !');
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
