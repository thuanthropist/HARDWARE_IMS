<?php

declare(strict_types=1);

namespace App\Models\Concerns;

/**
 * The VAT percentage actually applied to this stored record, derived from
 * its own subtotal/vat_amount rather than the live tax.vat_rate setting.
 * Once a quote/order/sale is priced, the rate charged is fixed — showing
 * today's setting next to yesterday's amount would misrepresent what the
 * customer was actually charged if the rate has since changed.
 */
trait HasVatRate
{
    public function vatRatePercent(): int
    {
        $subtotal = (float) $this->subtotal;

        if ($subtotal <= 0) {
            return (int) round(setting('tax.vat_rate', 0.18) * 100);
        }

        return (int) round(((float) $this->vat_amount / $subtotal) * 100);
    }
}
