<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Actions;

use Akira\LaravelLicense\Pipelines\LicenseUsageValidationPipeline;
use Akira\LaravelLicense\ValueObjects\DomainName;
use Akira\LaravelLicense\ValueObjects\LicenseContext;
use Akira\LaravelLicense\ValueObjects\LicenseKey;
use Akira\LaravelLicense\ValueObjects\MachineFingerprint;

final readonly class ValidateUsageAction
{
    public function __construct(private LicenseUsageValidationPipeline $pipeline) {}

    public function handle(LicenseKey $key, MachineFingerprint $machineFingerprint, ?DomainName $domain = null): LicenseContext
    {
        $ctx = new LicenseContext(
            key: $key,
            domain: $domain,
            machineFingerprint: $machineFingerprint
        );

        return $this->pipeline->process($ctx);
    }
}
