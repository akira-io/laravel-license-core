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

    /**
     * @param  array<string, mixed>  $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            lifetime: $config['lifetime'] ?? null,
            annual: $config['annual'] ?? null,
            subscription: $config['subscription'] ?? 30,
            trial: $config['trial'] ?? 7,
            credits: $config['credits'] ?? null,
        );
    }

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
