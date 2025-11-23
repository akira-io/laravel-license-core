<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\ValueObjects;

final readonly class LicenseMeta
{
    /** @param array<string, mixed>|null $data */
    public function __construct(public ?array $data) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}
