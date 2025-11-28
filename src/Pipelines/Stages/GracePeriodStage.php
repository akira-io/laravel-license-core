<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Pipelines\Stages;

use Akira\LaravelLicense\Contracts\LicenseValidatorStage;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Exceptions\LicenseExpiredException;
use Akira\LaravelLicense\Exceptions\LicenseNotLoadedException;
use Akira\LaravelLicense\ValueObjects\LicenseContext;

final class GracePeriodStage implements LicenseValidatorStage
{
    public function __invoke(LicenseContext $context): LicenseContext
    {
        $license = $context->license ?? throw LicenseNotLoadedException::create();
        $type = $license->typeEnum();

        if (in_array($type, [LicenseType::LIFETIME, LicenseType::ANNUAL, LicenseType::CREDITS], true)) {
            return $context;
        }

        if ($license->isExpired() && ! $license->inGracePeriod()) {
            throw LicenseExpiredException::withGracePeriod();
        }

        return $context;
    }
}
