<?php

namespace Mrrh\LicenseClient\Tests;

use Mrrh\LicenseClient\Facades\License;
use Mrrh\LicenseClient\LicenseClientServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [LicenseClientServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return ['License' => License::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('cache.default', 'array');
        $app['config']->set('license-client.token_path', sys_get_temp_dir().'/license-client-tests/license.token');
        $app['config']->set('license-client.license_server_url', 'https://licenses.example.tz');
        $app['config']->set('license-client.api_key', 'test-api-key');
        $app['config']->set('license-client.app_secret', 'test-app-secret');
        $app['config']->set('license-client.product_code', 'EOFF');
    }

    protected function tearDown(): void
    {
        $path = sys_get_temp_dir().'/license-client-tests/license.token';

        if (file_exists($path)) {
            unlink($path);
        }

        parent::tearDown();
    }
}
