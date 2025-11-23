<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\ValueObjects;

use Illuminate\Support\Carbon;

final readonly class UpdateEntitlement
{
    public function __construct(
        public ?Carbon $updatesUntil,
        public bool $fallbackMode,
    ) {}

    public function canInstall(Carbon $releaseDate): bool
    {
        if ($this->updatesUntil === null) {
            return true;
        }

        return $releaseDate->lessThanOrEqualTo($this->updatesUntil);
    }
}
