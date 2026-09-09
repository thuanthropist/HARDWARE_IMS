<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ProductBatch;
use App\Models\User;
use App\Notifications\UpcomingBatchExpiryNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckUpcomingBatchExpiries extends Command
{
    protected $signature = 'inventory:check-expiries';

    protected $description = 'Notify Admin and Manager users about batches expiring within the configured warning window';

    public function handle(): int
    {
        if (! setting('notifications.batch_expiry_enabled', true)) {
            $this->info('Batch-expiry notifications are disabled in Settings.');

            return self::SUCCESS;
        }

        $days = (int) config('inventory.expiry_warning_days');

        $batches = ProductBatch::query()
            ->expiringWithin($days)
            ->with(['productVariant.product', 'warehouse'])
            ->orderBy('expiry_date')
            ->get();

        if ($batches->isEmpty()) {
            $this->info("No batches expiring within {$days} days.");

            return self::SUCCESS;
        }

        $recipients = User::role(setting('notifications.batch_expiry_roles', ['Admin', 'Manager']))->get();

        if ($recipients->isEmpty()) {
            $this->warn('No Admin or Manager users to notify.');

            return self::SUCCESS;
        }

        Notification::send($recipients, new UpcomingBatchExpiryNotification($batches));

        $this->info("Notified {$recipients->count()} user(s) about {$batches->count()} batch(es) expiring within {$days} days.");

        return self::SUCCESS;
    }
}
