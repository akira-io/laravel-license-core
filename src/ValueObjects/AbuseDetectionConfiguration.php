<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\ValueObjects;

/** @param list<string> $eventsToMonitor */
final readonly class AbuseDetectionConfiguration
{
    /**
     * @param  list<string>  $eventsToMonitor
     */
    public function __construct(
        public bool $enabled,
        public int $windowMinutes,
        public int $activationThreshold,
        public array $eventsToMonitor,
        public string $actionOnAbuse,
    ) {}
}
