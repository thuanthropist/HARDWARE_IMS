<?php

namespace Mrrh\LicenseClient\Console\Commands;

use Illuminate\Console\Command;
use Mrrh\LicenseClient\Console\Commands\Concerns\ResolvesInstallationDetails;
use Mrrh\LicenseClient\LicenseManager;
use Mrrh\LicenseClient\Services\ApiClient;
use Mrrh\LicenseClient\Services\ServerFingerprint;
use Mrrh\LicenseClient\Services\TokenStore;
use Mrrh\LicenseClient\Services\TokenVerifier;

class ActivateLicense extends Command
{
    use ResolvesInstallationDetails;

    protected $signature = 'license:activate {license_key : The license key to activate, e.g. EOFF-XXXX-XXXX-XXXX-XXXX}';

    protected $description = 'Activate this installation against the License Server and store the signed token locally.';

    public function handle(
        ApiClient $api,
        TokenVerifier $verifier,
        TokenStore $store,
        ServerFingerprint $fingerprint,
        LicenseManager $manager,
    ): int {
        $licenseKey = trim((string) $this->argument('license_key'));
        $domain = $this->currentDomain();
        $serverFingerprint = $fingerprint->generate();
        $appVersion = (string) config('license-client.app_version', '1.0.0');

        $this->info("Activating {$licenseKey} for domain [{$domain}]...");

        $result = $api->activate($licenseKey, $domain, $serverFingerprint, $appVersion);

        if (! $result->ok) {
            $this->error('Activation failed: '.$result->message());

            return self::FAILURE;
        }

        $token = $result->body['signed_token'] ?? null;

        if (! is_string($token) || $token === '') {
            $this->error('The License Server did not return a signed token.');

            return self::FAILURE;
        }

        $claims = $verifier->verify($token);

        if ($claims === null) {
            $this->error('Received a token but its signature did not verify against the configured public key. Refusing to store it — double check config/license-client.php\'s public_key matches this product on the License Server.');

            return self::FAILURE;
        }

        if (! $this->isFresh($claims)) {
            $this->error('The received token failed its freshness check (possible stale or replayed response). Refusing to store it — please try again.');

            return self::FAILURE;
        }

        $store->write($token);
        $manager->forgetCache();

        $this->info('License activated. Token stored at '.config('license-client.token_path'));

        $expiresAt = $claims['expires_at'] ?? null;
        $this->line($expiresAt ? "Expires: {$expiresAt}" : 'This license does not expire.');

        if (! empty($claims['features_enabled'])) {
            $this->line('Features enabled: '.implode(', ', $claims['features_enabled']));
        }

        return self::SUCCESS;
    }
}
