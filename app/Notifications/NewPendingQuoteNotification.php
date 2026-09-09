<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewPendingQuoteNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Quote $quote)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_quote',
            'title' => "New quote request {$this->quote->quote_number} from {$this->quote->name}",
            'url' => route('quotes.show', $this->quote),
        ];
    }
}
