<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\ValueObjects;

final readonly class LicenseTypeConfiguration
{
    public function __construct(
        public bool $requiresActivation,
        public bool $requiresUpdateCheck,
        public bool $supportsGracePeriod,
        public bool $fallbackOnExpiry = false,
    ) {}

    /**
     * @param array<string, mixed> $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            requiresActivation: (bool) ($config['requires_activation'] ?? false),
            requiresUpdateCheck: (bool) ($config['requires_update_check'] ?? false),
            supportsGracePeriod: (bool) ($config['supports_grace_period'] ?? false),
            fallbackOnExpiry: (bool) ($config['fallback_on_expiry'] ?? false),
        );
    }
}