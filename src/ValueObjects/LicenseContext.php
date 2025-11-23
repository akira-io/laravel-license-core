<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\ValueObjects;

use Akira\LaravelLicense\Models\License;

final readonly class LicenseContext
{
    public function __construct(
        public LicenseKey $key,
        public ?DomainName $domain,
        public ?MachineFingerprint $machine,
        public ?string $ip,
        public ?string $ua,
        public ?License $license = null,
    ) {}

    public function withLicense(License $license): self
    {
        return new self($this->key, $this->domain, $this->machine, $this->ip, $this->ua, $license);
    }
}
