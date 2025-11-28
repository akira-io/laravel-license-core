<?php

declare(strict_types=1);

namespace Akira\LaravelLicense;

use Akira\LaravelLicense\Actions\ActivateLicenseAction;
use Akira\LaravelLicense\Actions\ConsumeCreditsAction;
use Akira\LaravelLicense\Actions\RotateLicenseKeyAction;
use Akira\LaravelLicense\Actions\ValidateUpdateAction;
use Akira\LaravelLicense\Actions\ValidateUsageAction;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\ValueObjects\DomainName;
use Akira\LaravelLicense\ValueObjects\LicenseKey;
use Akira\LaravelLicense\ValueObjects\MachineFingerprint;
use Carbon\CarbonInterface;
use InvalidArgumentException;
use Throwable;

final readonly class LaravelLicense
{
    public function __construct(
        private ValidateUsageAction $validateUsage,
        private ValidateUpdateAction $validateUpdate,
        private ActivateLicenseAction $activate,
        private ConsumeCreditsAction $consumeCredits,
        private RotateLicenseKeyAction $rotateKey,
    ) {}

    public function validateUsage(
        string $key,
        string $machine,
        ?string $domain = null,
        bool $activate = true,
    ): bool {
        try {
            $licenseKey = LicenseKey::fromString($key);
            $machineFingerprint = MachineFingerprint::fromRaw($machine) ?? throw new InvalidArgumentException('Machine fingerprint is required');
            $domainName = $domain ? DomainName::fromUrlOrHost($domain) : null;

            $context = $this->validateUsage->handle(
                key: $licenseKey,
                machineFingerprint: $machineFingerprint,
                domain: $domainName
            );

            if ($activate) {
                $license = $context->license ?? $this->findLicenseByKey($licenseKey);

                $this->activate->handle(
                    license: $license,
                    domain: $domainName,
                    machineFingerPrint: $machineFingerprint
                );
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function validateUpdate(
        string $key,
        CarbonInterface $releaseDate,
        ?string $domain = null,
        ?string $machine = null,
    ): bool {
        try {
            $licenseKey = LicenseKey::fromString($key);
            $machineFingerprint = $machine ? MachineFingerprint::fromRaw($machine) : null;
            $domainName = $domain ? DomainName::fromUrlOrHost($domain) : null;

            $this->validateUpdate->handle(
                $licenseKey,
                $releaseDate->toDateTimeString(),
                $domainName,
                $machineFingerprint
            );

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function consumeCredits(string $key, int $amount): bool
    {
        try {
            $license = $this->findLicenseByKey(LicenseKey::fromString($key));

            return $this->consumeCredits->handle($license, $amount);
        } catch (Throwable) {
            return false;
        }
    }

    public function rotateKey(string $key): ?string
    {
        try {
            $license = $this->findLicenseByKey(LicenseKey::fromString($key));

            return $this->rotateKey->handle($license);
        } catch (Throwable) {
            return null;
        }
    }

    private function findLicenseByKey(LicenseKey $key): License
    {
        return License::query()
            ->where('key', (string) $key)
            ->firstOrFail();
    }
}
