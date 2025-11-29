<?php

declare(strict_types=1);

namespace Akira\LaravelLicense;

use Akira\LaravelLicense\Actions\ActivateLicenseAction;
use Akira\LaravelLicense\Actions\ConsumeCreditsAction;
use Akira\LaravelLicense\Actions\CreateLicenseAction;
use Akira\LaravelLicense\Actions\RotateLicenseKeyAction;
use Akira\LaravelLicense\Actions\UpdateLicenseAction;
use Akira\LaravelLicense\Actions\ValidateUpdateAction;
use Akira\LaravelLicense\Actions\ValidateUsageAction;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\ValueObjects\DomainName;
use Akira\LaravelLicense\ValueObjects\LicenseData;
use Akira\LaravelLicense\ValueObjects\LicenseKey;
use Akira\LaravelLicense\ValueObjects\LicenseRequestData;
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
        private CreateLicenseAction $createLicense,
        private UpdateLicenseAction $updateLicense,
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
                $license = $context->license ?? $this->findByKey($licenseKey);

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
        LicenseRequestData $requestData,
        CarbonInterface $releaseDate,
    ): bool {
        try {

            $this->validateUpdate->handle(
                key: LicenseKey::fromString($requestData->key->key),
                releaseDate: $releaseDate->toDateString(),
                domain: $requestData->domain,
                machine: $requestData->machine,
            );

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function consumeCredits(string $key, int $amount): bool
    {
        try {
            $license = $this->findByKey(LicenseKey::fromString($key));

            return $this->consumeCredits->handle($license, $amount);
        } catch (Throwable) {
            return false;
        }
    }

    public function rotateKey(string $key): ?string
    {
        try {
            $license = $this->findByKey(LicenseKey::fromString($key));

            return $this->rotateKey->handle($license);
        } catch (Throwable) {
            return null;
        }
    }

    public function create(LicenseData $data): License
    {
        return $this->createLicense->handle($data);
    }

    public function createWithAutoKey(LicenseData $data): License
    {
        return $this->createLicense->handleWithAutoKey($data);
    }

    public function update(License $license, LicenseData $data): License
    {
        return $this->updateLicense->handle($license, $data);
    }

    public function updateByKey(string $key, LicenseData $data): ?License
    {
        try {
            $license = $this->findByKey(LicenseKey::fromString($key));

            return $this->updateLicense->handle($license, $data);
        } catch (Throwable) {
            return null;
        }
    }

    public function activate(LicenseRequestData $requestData): bool
    {
        try {

            $this->activate->handle(
                license: $requestData->key,
                domain: $requestData->domain,
                machineFingerPrint: $requestData->machine,
            );

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function findByKey(LicenseKey $key): License
    {
        return License::query()
            ->where('key', (string) $key)
            ->firstOrFail();
    }
}
