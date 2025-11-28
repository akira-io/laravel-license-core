<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\ValueObjects;

final readonly class CreditsConfiguration
{
    public function __construct(
        public bool $allowPartialConsumption,
        public bool $allowRefund,
    ) {}

    /**
     * @param array<string, mixed> $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            allowPartialConsumption: (bool) ($config['allow_partial_consumption'] ?? false),
            allowRefund: (bool) ($config['allow_refund'] ?? false),
        );
    }
}