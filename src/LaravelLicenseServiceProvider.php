<?php

declare(strict_types=1);

namespace Akira\LaravelLicense;

use Akira\LaravelLicense\Commands\LaravelLicenseCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class LaravelLicenseServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-license')
            ->hasConfigFile('license')
            ->hasMigrations([
                'create_licenses_table',
            ])
            ->hasCommand(LaravelLicenseCommand::class);
    }
}
