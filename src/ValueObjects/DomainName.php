<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\ValueObjects;

final readonly class DomainName
{
    public function __construct(public string $host) {}

    public static function fromUrlOrHost(?string $domain): ?self
    {
        if (! $domain) {
            return null;
        }

        $host = parse_url($domain, PHP_URL_HOST) ?? $domain;

        if (! $host) {
            return null;
        }

        return new self($host);
    }
}
