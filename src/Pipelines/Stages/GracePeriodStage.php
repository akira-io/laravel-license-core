<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Pipelines\Stages;

use Akira\LaravelLicense\Contracts\LicenseValidatorStage;
use Akira\LaravelLicense\Exceptions\LicenseExpiredException;
use Akira\LaravelLicense\Exceptions\LicenseNotLoadedException;
use Akira\LaravelLicense\ValueObjects\GracePeriodConfiguration;
use Akira\LaravelLicense\ValueObjects\LicenseContext;

final readonly class GracePeriodStage implements LicenseValidatorStage
{
    public function __construct(private GracePeriodConfiguration $gracePeriodConfig) {}

    public function __invoke(LicenseContext $context): LicenseContext
    {
        $license = $context->license ?? throw LicenseNotLoadedException::create();
        $type = $license->typeEnum();

        $hasGracePeriod = $this->gracePeriodConfig->getDaysForType($type->value) !== null;

        if (! $hasGracePeriod) {
            return $context;
        }

        if ($license->isExpired() && ! $license->inGracePeriod()) {
            throw LicenseExpiredException::withGracePeriod();
        }

        return $context;
    }
}
