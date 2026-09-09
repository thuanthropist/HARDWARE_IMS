<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\QuoteExpiringSoonMail;
use App\Models\Quote;
use App\Models\User;
use App\Notifications\QuoteAboutToExpireNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class NotifyExpiringQuotes extends Command
{
    protected $signature = 'quotes:notify-expiring';

    protected $description = 'Remind customers and staff about sent quotes expiring within 2 days that are still unanswered';

    public function handle(): int
    {
        $quotes = Quote::query()
            ->where('status', 'sent')
            ->whereNotNull('valid_until')
            ->whereNull('expiry_reminder_sent_at')
            ->whereBetween('valid_until', [now()->toDateString(), now()->addDays(2)->toDateString()])
            ->get();

        if ($quotes->isEmpty()) {
            $this->info('No quotes expiring soon that need a reminder.');

            return self::SUCCESS;
        }

        $notifyStaff = setting('notifications.quote_expiring_enabled', true);
        $staff = $notifyStaff ? User::role(setting('notifications.quote_expiring_roles', ['Admin', 'Manager']))->get() : collect();

        foreach ($quotes as $quote) {
            Mail::to($quote->email)->send(new QuoteExpiringSoonMail($quote));

            if ($staff->isNotEmpty()) {
                Notification::send($staff, new QuoteAboutToExpireNotification($quote));
            }

            $quote->update(['expiry_reminder_sent_at' => now()]);
        }

        $this->info("Sent expiry reminders for {$quotes->count()} quote(s).");

        return self::SUCCESS;
    }
}
