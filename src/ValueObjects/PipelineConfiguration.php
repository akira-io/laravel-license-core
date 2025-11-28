<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\ValueObjects;

/** @param list<string> $usageStages @param list<string> $updateStages */
final readonly class PipelineConfiguration
{
    /**
     * @param  list<string>  $usageStages
     * @param  list<string>  $updateStages
     */
    public function __construct(
        public array $usageStages,
        public array $updateStages,
    ) {}

    /**
     * @param  array<string, mixed>  $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            usageStages: (array) ($config['usage'] ?? [
                'resolve_license',
                'status_check',
                'expiration_usage',
                'grace_period',
                'domain_check',
                'machine_check',
                'credits_usage',
                'abuse_heuristics',
            ]),
            updateStages: (array) ($config['update'] ?? [
                'resolve_license',
                'status_check',
                'expiration_usage',
                'grace_period',
                'update_window',
            ]),
        );
    }
}
