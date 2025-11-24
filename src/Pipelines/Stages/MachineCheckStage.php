<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Pipelines\Stages;

use Akira\LaravelLicense\Contracts\LicenseValidatorStage;
use Akira\LaravelLicense\Exceptions\ActivationLimitReachedException;
use Akira\LaravelLicense\Exceptions\LicenseNotLoadedException;
use Akira\LaravelLicense\ValueObjects\LicenseContext;

final class MachineCheckStage implements LicenseValidatorStage
{
    public function __invoke(LicenseContext $context): LicenseContext
    {
        $license = $context->license ?? throw LicenseNotLoadedException::create();

        if (! $context->machine) {
            return $context;
        }

        $hash = $context->machine->hash;

        $existing = $license->activations()
            ->where('machine_hash', $hash)
            ->exists();

        if ($existing) {
            return $context;
        }

        $count = $license->activations()->count();

        if ($count >= $license->max_activations) {
            throw ActivationLimitReachedException::create();
        }

        return $context;
    }
}
