<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Pipelines\Stages;

use Akira\LaravelLicense\Contracts\LicenseValidatorStage;
use Akira\LaravelLicense\Exceptions\LicenseNotFoundException;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\ValueObjects\LicenseContext;

final class ResolveLicenseStage implements LicenseValidatorStage
{
    public function __invoke(LicenseContext $context): LicenseContext
    {
        $license = License::query()
            ->where('key', (string) $context->key)
            ->first();

        if (! $license) {
            throw LicenseNotFoundException::create();
        }

        return $context->withLicense($license);
    }
}
