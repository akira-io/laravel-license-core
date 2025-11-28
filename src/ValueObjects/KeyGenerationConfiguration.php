<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\ValueObjects;

final readonly class KeyGenerationConfiguration
{
    public function __construct(
        public string $prefix,
        public string $format,
    ) {}

    public function isValidFormat(): bool
    {
        return in_array($this->format, ['uuid', 'sequential'], true);
    }
}
