<?php

namespace Mrrh\LicenseClient\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use Mrrh\LicenseClient\LicenseManager;

class GraceBanner extends Component
{
    public function __construct(protected LicenseManager $license)
    {
    }

    public function render(): View
    {
        return view('license-client::components.grace-banner', [
            'show' => $this->license->isInGracePeriod(),
            'daysUntilExpiry' => $this->license->daysUntilExpiry(),
        ]);
    }
}
