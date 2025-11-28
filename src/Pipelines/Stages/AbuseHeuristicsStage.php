<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Pipelines\Stages;

use Akira\LaravelLicense\Contracts\LicenseValidatorStage;
use Akira\LaravelLicense\Enums\LicenseEventType;
use Akira\LaravelLicense\Models\LicenseEvent;
use Akira\LaravelLicense\ValueObjects\LicenseContext;

final class AbuseHeuristicsStage implements LicenseValidatorStage
{
    public function __invoke(LicenseContext $context): LicenseContext
    {
        $license = $context->license;

        if (! $license) {
            return $context;
        }

        $events = $license->events()
            ->where('type', LicenseEventType::ACTIVATED->value)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->count();

        if ($events >= 10) {
            LicenseEvent::query()
                ->create([
                    'license_id' => $license->id,
                    'type' => LicenseEventType::ABUSE_DETECTED->value,
                    'payload' => ['activations_last_10min' => $events],
                    'created_at' => now(),
                ]);
        }

        return $context;
    }
}
