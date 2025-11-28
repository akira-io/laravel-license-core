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
}
