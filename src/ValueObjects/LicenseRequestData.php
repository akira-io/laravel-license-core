<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\ValueObjects;

use Akira\LaravelLicense\Models\License;

final class LicenseRequestData
{
    public function __construct(
        public License $key,
        public MachineFingerprint $machine,
        public ?DomainName $domain,
    ) {}

    /** @param array<string, string> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            key: license()->findByKey(LicenseKey::fromString($data['key'])),
            machine: MachineFingerprint::fromRaw($data['machine']),
            domain: DomainName::fromUrlOrHost($data['domain']),
        );
    }

    /** @return array<string, License|MachineFingerprint|DomainName|null> */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'machine' => $this->machine,
            'domain' => $this->domain,
        ];
    }
}
