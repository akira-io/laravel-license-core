<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Pipelines\Stages;

use Akira\LaravelLicense\Contracts\LicenseValidatorStage;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Exceptions\LicenseExpiredException;
use Akira\LaravelLicense\Exceptions\LicenseNotLoadedException;
use Akira\LaravelLicense\ValueObjects\LicenseContext;

final class ExpirationUsageStage implements LicenseValidatorStage
{
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

        if ($license->isExpired() && ! $license->inGracePeriod()) {
            throw LicenseExpiredException::create();
        }

        return $context;
    }
}
