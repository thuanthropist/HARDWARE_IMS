<?php

namespace Mandray\LicenseClient\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mandray\LicenseClient\LicenseManager;
use Mandray\LicenseClient\Services\LicenseActivationWizard;

/**
 * JSON endpoints backing the activation-wizard Blade partial — each one is a
 * single step the wizard runs and reports on independently. See
 * LicenseActivationWizard for what each step actually checks.
 */
class ActivationWizardController extends Controller
{
    public function __construct(private readonly LicenseActivationWizard $wizard)
    {
    }

    public function checkConfig(): JsonResponse
    {
        return response()->json($this->wizard->checkConfig());
    }

    public function checkCrypto(): JsonResponse
    {
        return response()->json($this->wizard->checkCrypto());
    }

    public function checkConnectivity(): JsonResponse
    {
        return response()->json($this->wizard->checkConnectivity());
    }

    public function activate(Request $request, LicenseManager $manager): JsonResponse
    {
        $data = $request->validate([
            'license_key' => ['required', 'string', 'max:255'],
        ]);

        $result = $this->wizard->activate($data['license_key']);

        if ($result['ok']) {
            $manager->forgetCache();
        }

        return response()->json($result, $result['ok'] ? 200 : 422);
    }
}
