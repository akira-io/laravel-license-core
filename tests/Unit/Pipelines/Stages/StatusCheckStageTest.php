<?php

declare(strict_types=1);

use Akira\LaravelLicense\Enums\LicenseStatus;
use Akira\LaravelLicense\Exceptions\LicenseNotLoadedException;
use Akira\LaravelLicense\Exceptions\LicenseRevokedException;
use Akira\LaravelLicense\Exceptions\LicenseSuspendedException;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Pipelines\Stages\StatusCheckStage;
use Akira\LaravelLicense\ValueObjects\LicenseContext;
use Akira\LaravelLicense\ValueObjects\LicenseKey;

it('passes when license is active', function () {
    $license = License::factory()->create([
        'status' => LicenseStatus::ACTIVE->value,
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        machineFingerprint: null,
        license: $license
    );

    $stage = new StatusCheckStage();
    $result = $stage($context);

    expect($result)->toBe($context);
});

it('passes when license is expired', function () {
    $license = License::factory()->expired()->create();

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        machineFingerprint: null,
        license: $license
    );

    $stage = new StatusCheckStage();
    $result = $stage($context);

    expect($result)->toBe($context);
});

it('throws exception when license is not loaded', function () {
    $context = new LicenseContext(
        key: LicenseKey::fromString('XXXX-XXXX-XXXX-XXXX'),
        domain: null
    );

    $stage = new StatusCheckStage();
    $stage($context);
})->throws(LicenseNotLoadedException::class);

it('throws exception when license is revoked', function () {
    $license = License::factory()->create([
        'status' => LicenseStatus::REVOKED->value,
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        machineFingerprint: null,
        license: $license
    );

    $stage = new StatusCheckStage();
    $stage($context);
})->throws(LicenseRevokedException::class);

it('throws exception when license is suspended', function () {
    $license = License::factory()->create([
        'status' => LicenseStatus::SUSPENDED->value,
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        machineFingerprint: null,
        license: $license
    );

    $stage = new StatusCheckStage();
    $stage($context);
})->throws(LicenseSuspendedException::class);
