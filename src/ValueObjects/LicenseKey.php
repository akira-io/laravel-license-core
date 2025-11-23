<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\ValueObjects;

final readonly class LicenseKey
{
    public function __construct(public string $value) {}

    public function __toString(): string
    {
        return $this->value;
    }

    public static function fromString(string $key): self
    {
        return new self($key);
    }
}
