<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Actions;

use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseActivation;
use Akira\LaravelLicense\ValueObjects\DomainName;
use Akira\LaravelLicense\ValueObjects\MachineFingerprint;

final readonly class ActivateLicenseAction
{
    public function handle(
        License $license,
        ?DomainName $domain,
        MachineFingerprint $machineFingerPrint,
    ): LicenseActivation {
        return $license->activations()
            ->create([
                'domain' => $domain?->host,
                'machine_hash' => $machineFingerPrint->hash,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
    }
}
