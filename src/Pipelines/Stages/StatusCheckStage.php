<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Pipelines\Stages;

use Akira\LaravelLicense\Contracts\LicenseValidatorStage;
use Akira\LaravelLicense\Enums\LicenseStatus;
use Akira\LaravelLicense\Exceptions\LicenseNotLoadedException;
use Akira\LaravelLicense\Exceptions\LicenseRevokedException;
use Akira\LaravelLicense\Exceptions\LicenseSuspendedException;
use Akira\LaravelLicense\ValueObjects\LicenseContext;

final readonly class StatusCheckStage implements LicenseValidatorStage
{
    public function __invoke(LicenseContext $context): LicenseContext
    {
        $license = $context->license ?? throw LicenseNotLoadedException::create();

        $status = $license->statusEnum();

        if ($status === LicenseStatus::REVOKED) {
            throw LicenseRevokedException::create();
        }

        if ($status === LicenseStatus::SUSPENDED) {
            throw LicenseSuspendedException::create();
        }

        return $context;
    }
}
