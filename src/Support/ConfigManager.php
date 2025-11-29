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

    /** @return class-string */
    public function getLicenseModel(): string
    {
        /** @var class-string */
        return $this->getModelClass('license');
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return config("license.{$key}", $default);
    }

    /** @return array<string, mixed> */
    public function getAbuseDetectionConfig(): array
    {
        /** @var array<string, mixed> */
        return config('license.abuse_detection', [
            'enabled' => true,
            'window_minutes' => 10,
            'activation_threshold' => 10,
            'events_to_monitor' => ['activated'],
            'action_on_abuse' => 'log',
        ]);
    }

    /** @return array<string, mixed> */
    public function getGracePeriodConfig(): array
    {
        /** @var array<string, mixed> */
        return config('license.grace_period', [
            'lifetime' => null,
            'annual' => null,
            'subscription' => 30,
            'trial' => 7,
            'credits' => null,
        ]);
    }

    /** @return array<string, mixed> */
    public function getLicenseTypesConfig(): array
    {
        /** @var array<string, mixed> */
        return config('license.license_types', []);
    }

    /** @return array<string, mixed> */
    public function getLicenseTypeConfig(string $type): array
    {
        $config = $this->getLicenseTypesConfig();

        /** @var array<string, mixed> */
        return is_array($config[$type] ?? null) ? $config[$type] : [];
    }

    /** @return array<string, mixed> */
    public function getDomainValidationConfig(): array
    {
        /** @var array<string, mixed> */
        return config('license.domain_validation', [
            'pattern_type' => 'glob',
            'case_sensitive' => false,
        ]);
    }

    /** @return array<string, mixed> */
    public function getKeyGenerationConfig(): array
    {
        /** @var array<string, mixed> */
        return config('license.key_generation', [
            'prefix' => 'LIC',
            'format' => 'uuid',
        ]);
    }

    /** @return array<string, mixed> */
    public function getPipelineConfig(): array
    {
        /** @var array<string, mixed> */
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

    /** @return array<string, mixed> */
    public function getCreditsConfig(): array
    {
        /** @var array<string, mixed> */
        return config('license.credits', [
            'allow_partial_consumption' => false,
            'allow_refund' => false,
        ]);
    }

    public function getAbuseDetection(): AbuseDetectionConfiguration
    {
        $config = $this->getAbuseDetectionConfig();

        $windowMinutes = $config['window_minutes'] ?? 10;
        $activationThreshold = $config['activation_threshold'] ?? 10;
        $eventsToMonitor = $config['events_to_monitor'] ?? ['activated'];
        $actionOnAbuse = $config['action_on_abuse'] ?? 'log';

        /** @var list<string> $events */
        $events = is_array($eventsToMonitor) ? array_values($eventsToMonitor) : ['activated'];

        return new AbuseDetectionConfiguration(
            enabled: (bool) ($config['enabled'] ?? true),
            windowMinutes: is_int($windowMinutes) ? $windowMinutes : 10,
            activationThreshold: is_int($activationThreshold) ? $activationThreshold : 10,
            eventsToMonitor: $events,
            actionOnAbuse: is_string($actionOnAbuse) ? $actionOnAbuse : 'log',
        );
    }

    public function getGracePeriod(): GracePeriodConfiguration
    {
        $config = $this->getGracePeriodConfig();

        return new GracePeriodConfiguration(
            lifetime: isset($config['lifetime']) && is_int($config['lifetime']) ? $config['lifetime'] : null,
            annual: isset($config['annual']) && is_int($config['annual']) ? $config['annual'] : null,
            subscription: isset($config['subscription']) && is_int($config['subscription']) ? $config['subscription'] : 30,
            trial: isset($config['trial']) && is_int($config['trial']) ? $config['trial'] : 7,
            credits: isset($config['credits']) && is_int($config['credits']) ? $config['credits'] : null,
        );
    }

    public function getLicenseType(string $type): LicenseTypeConfiguration
    {
        $config = $this->getLicenseTypeConfig($type);

        return new LicenseTypeConfiguration(
            requiresActivation: (bool) ($config['requires_activation'] ?? false),
            requiresUpdateCheck: (bool) ($config['requires_update_check'] ?? false),
            supportsGracePeriod: (bool) ($config['supports_grace_period'] ?? false),
            fallbackOnExpiry: (bool) ($config['fallback_on_expiry'] ?? false),
        );
    }

    public function getDomainValidation(): DomainValidationConfiguration
    {
        $config = $this->getDomainValidationConfig();

        $patternType = $config['pattern_type'] ?? 'glob';
        $caseSensitive = $config['case_sensitive'] ?? false;

        return new DomainValidationConfiguration(
            patternType: is_string($patternType) ? $patternType : 'glob',
            caseSensitive: (bool) $caseSensitive,
        );
    }

    public function getKeyGeneration(): KeyGenerationConfiguration
    {
        $config = $this->getKeyGenerationConfig();

        $prefix = $config['prefix'] ?? 'LIC';
        $format = $config['format'] ?? 'uuid';

        return new KeyGenerationConfiguration(
            prefix: is_string($prefix) ? $prefix : 'LIC',
            format: is_string($format) ? $format : 'uuid',
        );
    }

    public function getPipeline(): PipelineConfiguration
    {
        $config = $this->getPipelineConfig();

        /** @var list<string> $usageStages */
        $usageStages = is_array($config['usage'] ?? false) ? $config['usage'] : [
            'resolve_license',
            'status_check',
            'expiration_usage',
            'grace_period',
            'domain_check',
            'machine_check',
            'credits_usage',
            'abuse_heuristics',
        ];

        /** @var list<string> $updateStages */
        $updateStages = is_array($config['update'] ?? false) ? $config['update'] : [
            'resolve_license',
            'status_check',
            'expiration_usage',
            'grace_period',
            'update_window',
        ];

        return new PipelineConfiguration(
            usageStages: $usageStages,
            updateStages: $updateStages,
        );
    }

    public function getCredits(): CreditsConfiguration
    {
        $config = $this->getCreditsConfig();

        return new CreditsConfiguration(
            allowPartialConsumption: (bool) ($config['allow_partial_consumption'] ?? false),
            allowRefund: (bool) ($config['allow_refund'] ?? false),
        );
    }
}
