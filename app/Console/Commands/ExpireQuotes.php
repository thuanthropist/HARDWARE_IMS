<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Quote;
use Illuminate\Console\Command;

class ExpireQuotes extends Command
{
    protected $signature = 'quotes:expire';

    protected $description = 'Mark sent quotes past their validity period as expired';

    public function handle(): int
    {
        $expired = Quote::query()
            ->where('status', 'sent')
            ->whereNotNull('valid_until')
            ->where('valid_until', '<', now()->toDateString())
            ->get();

        foreach ($expired as $quote) {
            $quote->update(['status' => 'expired']);
        }

        $this->info("Expired {$expired->count()} quote(s) past their validity period.");

        return self::SUCCESS;
    }
}
