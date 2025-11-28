<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\ValueObjects;

final readonly class KeyGenerationConfiguration
{
    public function __construct(
        public string $prefix,
        public string $format,
    ) {}

    /**
     * @param  array<string, mixed>  $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            prefix: (string) ($config['prefix'] ?? 'LIC'),
            format: (string) ($config['format'] ?? 'uuid'),
        );
    }

    public function isValidFormat(): bool
    {
        return in_array($this->format, ['uuid', 'sequential'], true);
    }
}
