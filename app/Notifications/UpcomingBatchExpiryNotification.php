<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class UpcomingBatchExpiryNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Collection $batches)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'batch_expiry',
            'title' => "{$this->batches->count()} batch(es) expiring soon",
            'url' => route('product-batches.index'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage())
            ->subject("Hardware IMS: {$this->batches->count()} batch(es) expiring soon")
            ->greeting("Hello {$notifiable->name},")
            ->line('The following batches are approaching their expiry date:');

        foreach ($this->batches as $batch) {
            $message->line(sprintf(
                '%s — Batch %s — %s units at %s — expires %s',
                $batch->productVariant->product->name,
                $batch->batch_number,
                number_format($batch->quantity),
                $batch->warehouse->name,
                $batch->expiry_date->format('d M Y')
            ));
        }

        return $message->line('Please review these items and plan for disposal, discount, or return as appropriate.');
    }
}
