<?php

declare(strict_types=1);

namespace Akira\LaravelLicense;

use Akira\LaravelLicense\Commands\LaravelLicenseCommand;
use Akira\LaravelLicense\Pipelines\LicenseUpdateValidationPipeline;
use Akira\LaravelLicense\Pipelines\LicenseUsageValidationPipeline;
use Akira\LaravelLicense\Pipelines\Stages\AbuseHeuristicsStage;
use Akira\LaravelLicense\Pipelines\Stages\CreditsUsageStage;
use Akira\LaravelLicense\Pipelines\Stages\DomainCheckStage;
use Akira\LaravelLicense\Pipelines\Stages\ExpirationUsageStage;
use Akira\LaravelLicense\Pipelines\Stages\GracePeriodStage;
use Akira\LaravelLicense\Pipelines\Stages\MachineCheckStage;
use Akira\LaravelLicense\Pipelines\Stages\ResolveLicenseStage;
use Akira\LaravelLicense\Pipelines\Stages\StatusCheckStage;
use Akira\LaravelLicense\Pipelines\Stages\UpdateWindowStage;
use Illuminate\Contracts\Foundation\Application;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class LaravelLicenseServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-license-core')
            ->hasConfigFile('license')
            ->hasTranslations()
            ->hasMigrations([
                'create_licenses_table',
            ])
            ->hasCommand(LaravelLicenseCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(LicenseUsageValidationPipeline::class, function (Application $app) {
            return new LicenseUsageValidationPipeline([
                $app->make(ResolveLicenseStage::class),
                $app->make(StatusCheckStage::class),
                $app->make(ExpirationUsageStage::class),
                $app->make(GracePeriodStage::class),
                $app->make(DomainCheckStage::class),
                $app->make(MachineCheckStage::class),
                $app->make(CreditsUsageStage::class),
                $app->make(AbuseHeuristicsStage::class),
            ]);
        });

        $this->app->singleton(LicenseUpdateValidationPipeline::class, function (Application $app) {
            return new LicenseUpdateValidationPipeline([
                $app->make(ResolveLicenseStage::class),
                $app->make(StatusCheckStage::class),
                $app->make(ExpirationUsageStage::class),
                $app->make(GracePeriodStage::class),
                $app->make(UpdateWindowStage::class),
            ]);
        });

        $this->app->singleton(LaravelLicense::class);
    }

    public function packageBooted(): void
    {
        $helpersFile = __DIR__.'/Support/helpers.php';
        if (file_exists($helpersFile)) {
            require_once $helpersFile;
        }
    }
}
