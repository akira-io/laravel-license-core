<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\ValueObjects;

final readonly class GracePeriodConfiguration
{
    public function __construct(
        public ?int $lifetime,
        public ?int $annual,
        public ?int $subscription,
        public ?int $trial,
        public ?int $credits,
    ) {}

    public function getDaysForType(string $type): ?int
    {
        return match ($type) {
            'lifetime' => $this->lifetime,
            'annual' => $this->annual,
            'subscription' => $this->subscription,
            'trial' => $this->trial,
            'credits' => $this->credits,
            default => null,
        };
    }
}
