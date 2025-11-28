<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Pipelines\Stages;

use Akira\LaravelLicense\Contracts\LicenseValidatorStage;
use Akira\LaravelLicense\Enums\LicenseEventType;
use Akira\LaravelLicense\Models\LicenseEvent;
use Akira\LaravelLicense\Support\ConfigManager;
use Akira\LaravelLicense\ValueObjects\LicenseContext;

final readonly class AbuseHeuristicsStage implements LicenseValidatorStage
{
    public function __construct(private ConfigManager $config) {}

    public function __invoke(LicenseContext $context): LicenseContext
    {
        $license = $context->license;

        if (! $license) {
            return $context;
        }

        $abuseConfig = $this->config->getAbuseDetection();

        if (! $abuseConfig->enabled) {
            return $context;
        }

        $events = $license->events()
            ->whereIn('type', $abuseConfig->eventsToMonitor)
            ->where('created_at', '>=', now()->subMinutes($abuseConfig->windowMinutes))
            ->count();

        if ($events >= $abuseConfig->activationThreshold) {
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
