<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\ValueObjects;

final readonly class MachineFingerprint
{
    public function __construct(public string $hash) {}

    public static function fromRaw(?string $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        return new self(hash('sha256', $value));
    }
}
