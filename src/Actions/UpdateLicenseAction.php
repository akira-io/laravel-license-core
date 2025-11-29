<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Actions;

use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\ValueObjects\LicenseData;

final readonly class UpdateLicenseAction
{
    public function handle(License $license, LicenseData $data): License
    {
        $license->update($data->toArray());

        /** @var License */
        return $license->fresh();
    }
}
