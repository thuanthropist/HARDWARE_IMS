<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mrrh\LicenseClient\Console\Commands\ActivateLicense;
use Mrrh\LicenseClient\LicenseManager;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class LicenseSettingsController extends Controller
{
    public function index(LicenseManager $license): View
    {
        return view('license-settings.index', [
            'status' => $license->status(),
            'isValid' => $license->isValid(),
            'isInGracePeriod' => $license->isInGracePeriod(),
            'expiresAt' => $license->expiresAt(),
            'daysUntilExpiry' => $license->daysUntilExpiry(),
            'maxUsers' => $license->maxUsers(),
            'features' => $license->claims()['features_enabled'] ?? [],
            'licenseKey' => $license->claims()['license_key'] ?? null,
            'productCode' => config('license-client.product_code'),
            'serverUrl' => config('license-client.license_server_url'),
        ]);
    }

    public function activate(Request $request, ActivateLicense $command): RedirectResponse
    {
        $data = $request->validate([
            'license_key' => ['required', 'string', 'max:255'],
        ]);

        // The package only registers its Artisan commands when
        // runningInConsole() (see LicenseClientServiceProvider::boot), so
        // Artisan::call() can never find "license:activate" from a web
        // request. Running the command object directly executes the exact
        // same handle() logic without needing it in the console registry.
        $command->setLaravel(app());
        $output = new BufferedOutput();
        $exitCode = $command->run(new ArrayInput(['license_key' => $data['license_key']]), $output);
        $outputText = trim($output->fetch());

        if ($exitCode !== 0) {
            return back()->with('error', $outputText ?: 'License activation failed.');
        }

        return redirect()->route('license.index')->with('success', $outputText ?: 'License activated.');
    }
}
