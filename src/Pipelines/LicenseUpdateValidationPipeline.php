<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Pipelines;

use Akira\LaravelLicense\Contracts\LicenseValidatorStage;
use Akira\LaravelLicense\ValueObjects\LicenseContext;
use Closure;

final readonly class LicenseUpdateValidationPipeline
{
    /** @param LicenseValidatorStage[] $stages */
    public function __construct(
        private array $stages,
    ) {}

    public function process(LicenseContext $context): LicenseContext
    {
        $pipeline = array_reduce(
            array_reverse($this->stages),
            fn (Closure $next, LicenseValidatorStage $stage) => fn (LicenseContext $ctx): LicenseContext => $stage($ctx),
            fn (LicenseContext $ctx): LicenseContext => $ctx,
        );

        return $pipeline($context);
    }
}
