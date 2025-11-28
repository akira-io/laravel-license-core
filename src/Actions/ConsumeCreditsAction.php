<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Actions;

use Akira\LaravelLicense\Exceptions\UsageNotConfiguredException;
use Akira\LaravelLicense\Models\License;

final readonly class ConsumeCreditsAction
{
    public function handle(License $license, int $amount): bool
    {
        $usage = $license->usages()->first();

        if (! $usage) {
            throw UsageNotConfiguredException::forCreditsLicense();
        }

        if ($usage->remaining() < $amount) {
            return false;
        }

        $usage->increment('consumed_units', $amount);

        return true;
    }
}
