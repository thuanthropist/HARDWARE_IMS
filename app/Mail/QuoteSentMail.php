<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuoteSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Quote $quote)
    {
    }

    public function build(): self
    {
        $pdf = Pdf::loadView('pdfs.quote', ['quote' => $this->quote]);

        return $this
            ->subject("Your Quote {$this->quote->quote_number} from Hardware IMS")
            ->view('emails.quote-sent')
            ->attachData($pdf->output(), "{$this->quote->quote_number}.pdf", ['mime' => 'application/pdf']);
    }
}
