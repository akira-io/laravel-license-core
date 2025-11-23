<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\ValueObjects;

final readonly class LicenseScopes
{
    /** @param string[]|null $scopes */
    public function __construct(public ?array $scopes) {}

    public function has(string $scope): bool
    {
        return in_array($scope, $this->scopes ?? [], true);
    }
}
