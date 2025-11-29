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
     * @return list<string>
     */
    public function getStagesForType(string $type): array
    {
        return match ($type) {
            'usage' => $this->usageStages,
            'update' => $this->updateStages,
            default => [],
        };
    }
}
