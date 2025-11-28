<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Actions;

use Akira\LaravelLicense\Pipelines\LicenseUpdateValidationPipeline;
use Akira\LaravelLicense\ValueObjects\DomainName;
use Akira\LaravelLicense\ValueObjects\LicenseContext;
use Akira\LaravelLicense\ValueObjects\LicenseKey;
use Akira\LaravelLicense\ValueObjects\MachineFingerprint;
use Illuminate\Support\Facades\Date;

final readonly class ValidateUpdateAction
{
    public function __construct(private LicenseUpdateValidationPipeline $pipeline) {}

    public function handle(LicenseKey $key, string $releaseDate, ?DomainName $domain = null, ?MachineFingerprint $machine = null): LicenseContext
    {
        $ctx = new LicenseContext(
            key: $key,
            domain: $domain,
            machineFingerprint: $machine,
            releaseDate: Date::parse($releaseDate),
        );

        return $this->pipeline->process($ctx);
    }
}
