<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Product;

class LowStockNotification extends Notification
{

    protected $products;

    public function __construct($products)
    {
        $this->products = $products;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
        ->subject('🛑 Critical Stock Alert!')
        ->greeting('Hello ' . $notifiable->name . ',')
        ->line('Some products have reached a critical stock level!')
        ->line('Here is the list of affected products:')
        ->line(implode("\n", $this->products->map(function ($product) {
            return '//=>' . $product->name . ' (Stock:' . $product->stock .')';
        })->toArray()))
        ->line('Please restock these products as soon as possible.');

    }
}
