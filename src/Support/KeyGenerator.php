<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Support;

use Illuminate\Support\Str;

final readonly class KeyGenerator
{
    public function __construct(private ConfigManager $configManager) {}

    public function generate(): string
    {
        $config = $this->configManager->getKeyGeneration();
        $separator = $config->prefix ? '-' : '';

        return match ($config->format) {
            'uuid' => $config->prefix.$separator.Str::uuid(),
            'sequential' => $this->generateSequential($config->prefix),
            default => $config->prefix.$separator.Str::uuid(),
        };
    }

    private function generateSequential(string $prefix): string
    {
        $timestamp = (string) now()->timestamp;
        $random = Str::random(8);
        $separator = $prefix ? '-' : '';

        return $prefix.$separator.$timestamp.$random;
    }
}
