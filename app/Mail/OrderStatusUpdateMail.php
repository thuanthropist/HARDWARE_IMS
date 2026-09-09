<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public string $headline, public string $body)
    {
    }

    public function build(): self
    {
        return $this
            ->subject("Order {$this->order->order_number} — {$this->headline}")
            ->view('emails.order-status-update');
    }
}
