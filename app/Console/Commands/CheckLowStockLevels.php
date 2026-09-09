<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockAlertNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckLowStockLevels extends Command
{
    protected $signature = 'inventory:check-low-stock';

    protected $description = 'Notify Admin and Manager users about products at or below their reorder point, per warehouse';

    public function handle(): int
    {
        if (! setting('notifications.low_stock_enabled', true)) {
            $this->info('Low-stock notifications are disabled in Settings.');

            return self::SUCCESS;
        }

        $products = Product::query()
            ->active()
            ->lowStock()
            ->with('department')
            ->orderBy('department_id')
            ->get();

        if ($products->isEmpty()) {
            $this->info('No products at or below their reorder point.');

            return self::SUCCESS;
        }

        $recipients = User::role(setting('notifications.low_stock_roles', ['Admin', 'Manager']))->get();

        if ($recipients->isEmpty()) {
            $this->warn('No Admin or Manager users to notify.');

            return self::SUCCESS;
        }

        Notification::send($recipients, new LowStockAlertNotification($products));

        $this->info("Notified {$recipients->count()} user(s) about {$products->count()} low-stock product(s).");

        return self::SUCCESS;
    }
}
