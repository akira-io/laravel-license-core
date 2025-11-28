<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Pipelines\Stages;

use Akira\LaravelLicense\Contracts\LicenseValidatorStage;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Exceptions\LicenseExpiredException;
use Akira\LaravelLicense\Exceptions\LicenseNotLoadedException;
use Akira\LaravelLicense\Support\ConfigManager;
use Akira\LaravelLicense\ValueObjects\LicenseContext;

final readonly class ExpirationUsageStage implements LicenseValidatorStage
{
    public function __construct(private ConfigManager $configManager) {}

    public function __invoke(LicenseContext $context): LicenseContext
    {
        $license = $context->license ?? throw LicenseNotLoadedException::create();

        $type = $license->typeEnum();

        if ($type === LicenseType::LIFETIME || $type === LicenseType::CREDITS) {
            return $context;
        }

        if ($type === LicenseType::ANNUAL) {
            if ($license->isExpired() && ! $license->fallback) {
                throw LicenseExpiredException::annual();
            }

            return $context;
        }

        $gracePeriodConfig = $this->configManager->getGracePeriod();
        $hasGracePeriod = $gracePeriodConfig->getDaysForType($type->value) !== null;

        if ($license->isExpired() && ! ($hasGracePeriod && $license->inGracePeriod())) {
            throw LicenseExpiredException::create();
        }

        return $context;
    }
}
