<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Actions;

use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Support\KeyGenerator;

final readonly class RotateLicenseKeyAction
{
    public function handle(License $license): string
    {
        $new = KeyGenerator::generate();

        $license->update(['key' => $new]);

        return $new;
    }
}
