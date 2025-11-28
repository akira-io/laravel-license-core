<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Contracts;

use Akira\LaravelLicense\ValueObjects\LicenseContext;

interface LicenseValidatorStage
{
    public function __invoke(LicenseContext $context): LicenseContext;
}
