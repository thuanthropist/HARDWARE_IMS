<?php

namespace Mrrh\LicenseClient\Facades;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Facade;
use Mrrh\LicenseClient\LicenseManager;

/**
 * @method static bool isValid()
 * @method static bool isInGracePeriod()
 * @method static string status()
 * @method static bool hasFeature(string $key)
 * @method static ?int maxUsers()
 * @method static ?Carbon expiresAt()
 * @method static ?int daysUntilExpiry()
 * @method static ?array claims()
 * @method static void forgetCache()
 *
 * @see LicenseManager
 */
class License extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LicenseManager::class;
    }
}
