<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\ValueObjects;

/** @param list<string> $eventsToMonitor */
final readonly class AbuseDetectionConfiguration
{
    /**
     * @param list<string> $eventsToMonitor
     */
    public function __construct(
        public bool $enabled,
        public int $windowMinutes,
        public int $activationThreshold,
        public array $eventsToMonitor,
        public string $actionOnAbuse,
    ) {}

    /**
     * @param array<string, mixed> $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            enabled: (bool) ($config['enabled'] ?? true),
            windowMinutes: (int) ($config['window_minutes'] ?? 10),
            activationThreshold: (int) ($config['activation_threshold'] ?? 10),
            eventsToMonitor: (array) ($config['events_to_monitor'] ?? ['activated']),
            actionOnAbuse: (string) ($config['action_on_abuse'] ?? 'log'),
        );
    }
}