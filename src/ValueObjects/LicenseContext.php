<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\ValueObjects;

use Akira\LaravelLicense\Models\License;
use Carbon\CarbonInterface;

final readonly class LicenseContext
{
    public function __construct(
        public LicenseKey $key,
        public ?DomainName $domain,
        public ?MachineFingerprint $machineFingerprint = null,
        public ?License $license = null,
        public ?CarbonInterface $releaseDate = null,
    ) {}

    public function withLicense(License $license): self
    {
        return new self(
            key: $this->key,
            domain: $this->domain,
            machineFingerprint: $this->machineFingerprint,
            license: $license,
            releaseDate: $this->releaseDate
        );
    }

    public function withReleaseDate(CarbonInterface $releaseDate): self
    {
        return new self(
            key: $this->key,
            domain: $this->domain,
            machineFingerprint: $this->machineFingerprint,
            license: $this->license,
            releaseDate: $releaseDate
        );
    }
}
