<?php

declare(strict_types=1);

namespace Akira\LaravelLicense;

use Akira\LaravelLicense\Commands\LaravelLicenseCommand;
use Akira\LaravelLicense\Contracts\LicenseValidatorStage;
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
use Akira\LaravelLicense\Support\ConfigManager;
use Akira\LaravelLicense\Support\KeyGenerator;
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
        $this->app->singleton('license.config-manager', ConfigManager::class);

        $this->app->singleton(KeyGenerator::class, function (Application $app) {
            return new KeyGenerator($app->make(ConfigManager::class));
        });

        $this->app->bind(AbuseHeuristicsStage::class, function (Application $app) {
            return new AbuseHeuristicsStage($app->make(ConfigManager::class)->getAbuseDetection());
        });

        $this->app->bind(GracePeriodStage::class, function (Application $app) {
            return new GracePeriodStage($app->make(ConfigManager::class)->getGracePeriod());
        });

        $this->app->bind(ExpirationUsageStage::class, function (Application $app) {
            return new ExpirationUsageStage($app->make(ConfigManager::class));
        });

        $this->app->bind(DomainCheckStage::class, function (Application $app) {
            return new DomainCheckStage($app->make(ConfigManager::class)->getDomainValidation());
        });

        $this->app->bind(CreditsUsageStage::class, function (Application $app) {
            return new CreditsUsageStage($app->make(ConfigManager::class));
        });

        $this->app->bind(UpdateWindowStage::class, function (Application $app) {
            return new UpdateWindowStage($app->make(ConfigManager::class));
        });

        $this->registerPipelineStageMapping();

        $this->app->singleton(LicenseUsageValidationPipeline::class, function (Application $app) {
            return new LicenseUsageValidationPipeline($this->resolvePipelineStages($app, 'usage'));
        });

        $this->app->singleton(LicenseUpdateValidationPipeline::class, function (Application $app) {
            return new LicenseUpdateValidationPipeline($this->resolvePipelineStages($app, 'update'));
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

    private function registerPipelineStageMapping(): void
    {
        $this->app->singleton('license.stage-mapping', function () {
            return [
                'resolve_license' => ResolveLicenseStage::class,
                'status_check' => StatusCheckStage::class,
                'expiration_usage' => ExpirationUsageStage::class,
                'grace_period' => GracePeriodStage::class,
                'domain_check' => DomainCheckStage::class,
                'machine_check' => MachineCheckStage::class,
                'credits_usage' => CreditsUsageStage::class,
                'abuse_heuristics' => AbuseHeuristicsStage::class,
                'update_window' => UpdateWindowStage::class,
            ];
        });
    }

    /**
     * @return array<int, LicenseValidatorStage>
     */
    private function resolvePipelineStages(Application $app, string $pipelineType): array
    {
        $configManager = $app->make(ConfigManager::class);
        $pipelineConfig = $configManager->getPipeline();
        $stages = $pipelineConfig->getStagesForType($pipelineType);
        /** @var array<string, class-string<LicenseValidatorStage>> $stageMapping */
        $stageMapping = $app->make('license.stage-mapping');

        /** @var array<int, LicenseValidatorStage> $result */
        $result = array_map(
            fn (string $stageName) => $app->make($stageMapping[$stageName] ?? $stageName),
            $stages
        );

        return $result;
    }
}
