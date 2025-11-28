<?php

declare(strict_types=1);

use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Exceptions\LicenseNotLoadedException;
use Akira\LaravelLicense\Exceptions\VersionNotCoveredException;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Pipelines\Stages\UpdateWindowStage;
use Akira\LaravelLicense\ValueObjects\LicenseContext;
use Akira\LaravelLicense\ValueObjects\LicenseKey;

covers(UpdateWindowStage::class);

it('passes for lifetime licenses regardless of release date', function () {
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

    $stage = new UpdateWindowStage();
    $result = $stage($context);

    expect($result)->toBe($context);
});

it('passes for credit licenses regardless of release date', function () {
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

    $stage = new UpdateWindowStage();
    $result = $stage($context);

    expect($result)->toBe($context);
});

it('throws exception when license is not loaded', function () {
    $context = new LicenseContext(
        key: LicenseKey::fromString('test-key-1234'),
        domain: null,
        machineFingerprint: null,
        license: null,
        releaseDate: now(),
    );

    $stage = new UpdateWindowStage();
    $stage($context);
})->throws(LicenseNotLoadedException::class);

it('throws exception when release date is null for subscription license', function () {
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

    $stage = new UpdateWindowStage();
    $stage($context);
})->throws(VersionNotCoveredException::class);

it('passes when release date is within update window', function () {
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

    $stage = new UpdateWindowStage();
    $result = $stage($context);

    expect($result)->toBe($context);
});

it('throws exception when release date is after update window', function () {
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

    $stage = new UpdateWindowStage();
    $stage($context);
})->throws(VersionNotCoveredException::class);

it('passes when release date equals expiry date', function () {
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

    $stage = new UpdateWindowStage();
    $result = $stage($context);

    expect($result)->toBe($context);
});
