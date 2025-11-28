<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Support;

use Akira\LaravelLicense\ValueObjects\AbuseDetectionConfiguration;
use Akira\LaravelLicense\ValueObjects\CreditsConfiguration;
use Akira\LaravelLicense\ValueObjects\DomainValidationConfiguration;
use Akira\LaravelLicense\ValueObjects\GracePeriodConfiguration;
use Akira\LaravelLicense\ValueObjects\KeyGenerationConfiguration;
use Akira\LaravelLicense\ValueObjects\LicenseTypeConfiguration;
use Akira\LaravelLicense\ValueObjects\PipelineConfiguration;
use Illuminate\Container\Attributes\Singleton;

#[Singleton]
final class ConfigManager
{
    public function getTableName(string $key): string
    {
        return config()->string("license.tables.{$key}", "license_{$key}s");
    }

    public function getLicenseTable(): string
    {
        return $this->getTableName('licenses');
    }

    public function getActivationsTable(): string
    {
        return $this->getTableName('activations');
    }

    public function getUsagesTable(): string
    {
        return $this->getTableName('usages');
    }

    public function getEventsTable(): string
    {
        return $this->getTableName('events');
    }

    public function getModelClass(string $key): string
    {
        return config()->string("license.models.{$key}", '');
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return config("license.{$key}", $default);
    }

    public function getAbuseDetectionConfig(): array
    {
        return config('license.abuse_detection', [
            'enabled' => true,
            'window_minutes' => 10,
            'activation_threshold' => 10,
            'events_to_monitor' => ['activated'],
            'action_on_abuse' => 'log',
        ]);
    }

    public function getGracePeriodConfig(): array
    {
        return config('license.grace_period', [
            'lifetime' => null,
            'annual' => null,
            'subscription' => 30,
            'trial' => 7,
            'credits' => null,
        ]);
    }

    public function getLicenseTypesConfig(): array
    {
        return config('license.license_types', []);
    }

    public function getLicenseTypeConfig(string $type): array
    {
        return $this->getLicenseTypesConfig()[$type] ?? [];
    }

    public function getDomainValidationConfig(): array
    {
        return config('license.domain_validation', [
            'pattern_type' => 'glob',
            'case_sensitive' => false,
        ]);
    }

    public function getKeyGenerationConfig(): array
    {
        return config('license.key_generation', [
            'prefix' => 'LIC',
            'format' => 'uuid',
        ]);
    }

    public function getPipelineConfig(): array
    {
        return config('license.pipeline', [
            'usage' => [
                'resolve_license',
                'status_check',
                'expiration_usage',
                'grace_period',
                'domain_check',
                'machine_check',
                'credits_usage',
                'abuse_heuristics',
            ],
            'update' => [
                'resolve_license',
                'status_check',
                'expiration_usage',
                'grace_period',
                'update_window',
            ],
        ]);
    }

    public function getCreditsConfig(): array
    {
        return config('license.credits', [
            'allow_partial_consumption' => false,
            'allow_refund' => false,
        ]);
    }

    public function getAbuseDetection(): AbuseDetectionConfiguration
    {
        return AbuseDetectionConfiguration::fromArray($this->getAbuseDetectionConfig());
    }

    public function getGracePeriod(): GracePeriodConfiguration
    {
        return GracePeriodConfiguration::fromArray($this->getGracePeriodConfig());
    }

    public function getLicenseType(string $type): LicenseTypeConfiguration
    {
        return LicenseTypeConfiguration::fromArray($this->getLicenseTypeConfig($type));
    }

    public function getDomainValidation(): DomainValidationConfiguration
    {
        return DomainValidationConfiguration::fromArray($this->getDomainValidationConfig());
    }

    public function getKeyGeneration(): KeyGenerationConfiguration
    {
        return KeyGenerationConfiguration::fromArray($this->getKeyGenerationConfig());
    }

    public function getPipeline(): PipelineConfiguration
    {
        return PipelineConfiguration::fromArray($this->getPipelineConfig());
    }

    public function getCredits(): CreditsConfiguration
    {
        return CreditsConfiguration::fromArray($this->getCreditsConfig());
    }
}
