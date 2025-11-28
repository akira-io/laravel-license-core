<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Pipelines\Stages;

use Akira\LaravelLicense\Contracts\LicenseValidatorStage;
use Akira\LaravelLicense\Enums\LicenseEventType;
use Akira\LaravelLicense\Models\LicenseEvent;
use Akira\LaravelLicense\ValueObjects\AbuseDetectionConfiguration;
use Akira\LaravelLicense\ValueObjects\LicenseContext;

final readonly class AbuseHeuristicsStage implements LicenseValidatorStage
{
    public function __construct(private AbuseDetectionConfiguration $abuseConfig) {}

    public function __invoke(LicenseContext $context): LicenseContext
    {
        $license = $context->license;

        if (! $license) {
            return $context;
        }

        if (! $this->abuseConfig->enabled) {
            return $context;
        }

        $events = $license->events()
            ->whereIn('type', $this->abuseConfig->eventsToMonitor)
            ->where('created_at', '>=', now()->subMinutes($this->abuseConfig->windowMinutes))
            ->count();

        if ($events >= $this->abuseConfig->activationThreshold) {
            LicenseEvent::query()
                ->create([
                    'license_id' => $license->id,
                    'type' => LicenseEventType::ABUSE_DETECTED->value,
                    'payload' => ['activations' => $events],
                    'created_at' => now(),
                ]);
        }

        return $context;
    }
}
