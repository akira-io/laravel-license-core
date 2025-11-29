<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\ValueObjects;

final readonly class UsageAmount
{
    public function __construct(
        public int $limit,
        public int $used
    ) {}

    public function remaining(): int
    {
        return max(0, $this->limit - $this->used);
    }

    public function hasEnough(int $amount): bool
    {
        return $this->remaining() >= $amount;
    }

    public function hasAny(): bool
    {
        return $this->remaining() > 0;
    }
}
