<?php

declare(strict_types=1);

use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Exceptions\LicenseNotLoadedException;
use Akira\LaravelLicense\Exceptions\VersionNotCoveredException;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Pipelines\Stages\UpdateWindowStage;
use Akira\LaravelLicense\Support\ConfigManager;
use Akira\LaravelLicense\ValueObjects\LicenseContext;
use Akira\LaravelLicense\ValueObjects\LicenseKey;

covers(UpdateWindowStage::class);

it('passes for lifetime licenses regardless of release date', function () {
    $configManager = resolve(ConfigManager::class);
    $stage = new UpdateWindowStage($configManager);

    $license = License::factory()->create([
        'type' => LicenseType::LIFETIME->value,
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        machineFingerprint: null,
        license: $license,
        releaseDate: now()->addYear(),
    );

    $result = $stage($context);

    expect($result)->toBe($context);
});

it('passes for credit licenses regardless of release date', function () {
    $configManager = resolve(ConfigManager::class);
    $stage = new UpdateWindowStage($configManager);

    $license = License::factory()->create([
        'type' => LicenseType::CREDITS->value,
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        machineFingerprint: null,
        license: $license,
        releaseDate: now()->addYear(),
    );

    $result = $stage($context);

    expect($result)->toBe($context);
});

it('throws exception when license is not loaded', function () {
    $configManager = resolve(ConfigManager::class);
    $stage = new UpdateWindowStage($configManager);

    $context = new LicenseContext(
        key: LicenseKey::fromString('test-key-1234'),
        domain: null,
        machineFingerprint: null,
        license: null,
        releaseDate: now(),
    );

    $stage($context);
})->throws(LicenseNotLoadedException::class);

it('throws exception when release date is null for subscription license', function () {
    $configManager = resolve(ConfigManager::class);
    $stage = new UpdateWindowStage($configManager);

    $license = License::factory()->create([
        'type' => LicenseType::SUBSCRIPTION->value,
        'expires_at' => now()->addYear(),
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        machineFingerprint: null,
        license: $license,
        releaseDate: null,
    );

    $stage($context);
})->throws(VersionNotCoveredException::class);

it('passes when release date is within update window', function () {
    $configManager = resolve(ConfigManager::class);
    $stage = new UpdateWindowStage($configManager);

    $license = License::factory()->create([
        'type' => LicenseType::SUBSCRIPTION->value,
        'expires_at' => now()->addYear(),
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        machineFingerprint: null,
        license: $license,
        releaseDate: now()->subMonth(),
    );

    $result = $stage($context);

    expect($result)->toBe($context);
});

it('throws exception when release date is after update window', function () {
    $configManager = resolve(ConfigManager::class);
    $stage = new UpdateWindowStage($configManager);

    $license = License::factory()->create([
        'type' => LicenseType::SUBSCRIPTION->value,
        'expires_at' => now()->subMonth(),
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        machineFingerprint: null,
        license: $license,
        releaseDate: now(),
    );

    $stage($context);
})->throws(VersionNotCoveredException::class);

it('passes when release date equals expiry date', function () {
    $configManager = resolve(ConfigManager::class);
    $stage = new UpdateWindowStage($configManager);

    $expiryDate = today();

    $license = License::factory()->create([
        'type' => LicenseType::SUBSCRIPTION->value,
        'expires_at' => $expiryDate,
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        machineFingerprint: null,
        license: $license,
        releaseDate: $expiryDate,
    );

    $result = $stage($context);

    expect($result)->toBe($context);
});
