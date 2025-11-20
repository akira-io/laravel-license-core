<?php

namespace Akira\LaravelLicense;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Akira\LaravelLicense\Commands\LaravelLicenseCommand;

class LaravelLicenseServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-license')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_license_table')
            ->hasCommand(LaravelLicenseCommand::class);
    }
}
