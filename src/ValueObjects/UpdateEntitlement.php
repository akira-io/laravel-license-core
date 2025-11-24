<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\ValueObjects;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

final readonly class UpdateEntitlement
{
    public function __construct(
        public ?CarbonInterface $updatesUntil,
        public bool $fallbackMode,
    ) {}

    public function canInstall(CarbonInterface $releaseDate): bool
    {
        if ($this->updatesUntil === null) {
            return true;
        }

        return $releaseDate->lessThanOrEqualTo($this->updatesUntil);
    }
}
