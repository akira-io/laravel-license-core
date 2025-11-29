<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Facades;

use Akira\LaravelLicense\LaravelLicense;
use Akira\LaravelLicense\Models\License as LicenseModel;
use Akira\LaravelLicense\ValueObjects\LicenseData;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool validateUsage(string $key, string $machine, ?string $domain = null, bool $activate = true)
 * @method static bool validateUpdate(string $key, CarbonInterface $releaseDate, ?string $domain = null, ?string $machine = null)
 * @method static bool consumeCredits(string $key, int $amount)
 * @method static string|null rotateKey(string $key)
 * @method static LicenseModel createLicense(LicenseData $data)
 * @method static LicenseModel createLicenseWithAutoKey(LicenseData $data)
 * @method static LicenseModel updateLicense(LicenseModel $license, LicenseData $data)
 * @method static LicenseModel|null updateLicenseByKey(string $key, LicenseData $data)
 *
 * @see LaravelLicense
 */
final class License extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LaravelLicense::class;
    }
}
