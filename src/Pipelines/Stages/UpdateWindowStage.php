<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Pipelines\Stages;

use Akira\LaravelLicense\Contracts\LicenseValidatorStage;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Exceptions\LicenseNotLoadedException;
use Akira\LaravelLicense\Exceptions\VersionNotCoveredException;
use Akira\LaravelLicense\ValueObjects\LicenseContext;
use Akira\LaravelLicense\ValueObjects\UpdateEntitlement;

final readonly class UpdateWindowStage implements LicenseValidatorStage
{
    public function __invoke(LicenseContext $context): LicenseContext
    {
        $license = $context->license ?? throw LicenseNotLoadedException::create();

        $type = $license->typeEnum();

        if ($type === LicenseType::LIFETIME || $type === LicenseType::CREDITS) {
            return $context;
        }

        throw_if($context->releaseDate === null, VersionNotCoveredException::class);

        $entitlement = new UpdateEntitlement(
            updatesUntil: $license->expires_at,
            fallbackMode: $license->fallback,
        );

        throw_unless($entitlement->canInstall($context->releaseDate), VersionNotCoveredException::class);

        return $context;
    }
}
