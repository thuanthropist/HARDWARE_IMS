<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class LowStockAlertNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Collection $products)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'low_stock',
            'title' => "{$this->products->count()} product(s) at or below reorder point",
            'url' => route('dashboard'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage())
            ->subject("Hardware IMS: {$this->products->count()} product(s) at or below reorder point")
            ->greeting("Hello {$notifiable->name},")
            ->line('The following products are at or below their reorder point:');

        foreach ($this->products as $product) {
            $suggested = max(0, ($product->reorder_point * setting('tax.reorder_multiplier', 2)) - $product->current_stock);

            $message->line(sprintf(
                '[%s] %s — stock %s / reorder point %s — suggested reorder qty %s',
                $product->department->name,
                $product->name,
                number_format($product->current_stock),
                number_format($product->reorder_point),
                number_format($suggested)
            ));
        }

        return $message->line('Consider raising a purchase order for these items.');
    }
}
