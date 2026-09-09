<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuoteAboutToExpireNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Quote $quote)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("Quote {$this->quote->quote_number} expires soon — no response yet")
            ->greeting("Hello {$notifiable->name},")
            ->line("Quote {$this->quote->quote_number} for {$this->quote->name} is still unanswered and expires on {$this->quote->valid_until->format('d M Y')}.")
            ->line('Consider following up with the customer before it lapses.')
            ->action('View Quote', route('quotes.show', $this->quote));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'quote_expiring',
            'title' => "Quote {$this->quote->quote_number} expires {$this->quote->valid_until->format('d M Y')} — still unanswered",
            'url' => route('quotes.show', $this->quote),
        ];
    }
}
