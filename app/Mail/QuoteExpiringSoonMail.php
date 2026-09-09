<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuoteExpiringSoonMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Quote $quote)
    {
    }

    public function build(): self
    {
        return $this
            ->subject("Your Quote {$this->quote->quote_number} Expires Soon")
            ->view('emails.quote-expiring-soon');
    }
}
