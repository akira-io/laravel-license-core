<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\ValueObjects;

final readonly class CreditsConfiguration
{
    public function __construct(
        public bool $allowPartialConsumption,
        public bool $allowRefund,
    ) {}
}
