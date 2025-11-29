<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Actions;

use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Support\ConfigManager;
use Akira\LaravelLicense\Support\KeyGenerator;
use Akira\LaravelLicense\ValueObjects\LicenseData;

final readonly class CreateLicenseAction
{
    public function __construct(
        private KeyGenerator $keyGenerator,
        private ConfigManager $configManager,
    ) {}

    public function handle(LicenseData $data): License
    {
        /** @var class-string<License> $modelClass */
        $modelClass = $this->configManager->getLicenseModel();

        return $modelClass::create($data->toArray());
    }

    public function handleWithAutoKey(LicenseData $data): License
    {
        $dataWithKey = new LicenseData(
            key: $this->keyGenerator->generate(),
            type: $data->type,
            status: $data->status,
            maxActivations: $data->maxActivations,
            maxSeats: $data->maxSeats,
            fallback: $data->fallback,
            scopes: $data->scopes,
            meta: $data->meta,
            expiresAt: $data->expiresAt,
            graceEndsAt: $data->graceEndsAt,
        );

        return $this->handle($dataWithKey);
    }
}
