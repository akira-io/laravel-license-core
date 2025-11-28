<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Pipelines\Stages;

use Akira\LaravelLicense\Contracts\LicenseValidatorStage;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Exceptions\InsufficientCreditsException;
use Akira\LaravelLicense\Exceptions\LicenseNotLoadedException;
use Akira\LaravelLicense\Exceptions\UsageNotConfiguredException;
use Akira\LaravelLicense\Models\LicenseUsage;
use Akira\LaravelLicense\ValueObjects\CreditsConfiguration;
use Akira\LaravelLicense\ValueObjects\LicenseContext;
use Akira\LaravelLicense\ValueObjects\UsageAmount;

final readonly class CreditsUsageStage implements LicenseValidatorStage
{
    public function __construct(
        private CreditsConfiguration $creditsConfig,
        private int $amountToConsume = 0,
    ) {}

    public function __invoke(LicenseContext $context): LicenseContext
    {
        $license = $context->license ?? throw LicenseNotLoadedException::create();

        if ($license->typeEnum() !== LicenseType::CREDITS) {
            return $context;
        }

        /** @var LicenseUsage|null $usage */
        $usage = $license->usages()->first();

        if (! $usage) {
            throw UsageNotConfiguredException::forCreditsLicense();
        }

        /** @var int $limit */
        $limit = $usage->limit;
        /** @var int $consumedUnits */
        $consumedUnits = $usage->consumed_units;

        $amount = new UsageAmount($limit, $consumedUnits);

        if (! $amount->hasEnough($this->amountToConsume)) {
            throw InsufficientCreditsException::create($this->amountToConsume, $amount->remaining());
        }

        return $context;
    }
}
