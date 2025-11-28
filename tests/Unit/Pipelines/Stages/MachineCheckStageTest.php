<?php

declare(strict_types=1);

use Akira\LaravelLicense\Exceptions\ActivationLimitReachedException;
use Akira\LaravelLicense\Exceptions\LicenseNotLoadedException;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseActivation;
use Akira\LaravelLicense\Pipelines\Stages\MachineCheckStage;
use Akira\LaravelLicense\ValueObjects\LicenseContext;
use Akira\LaravelLicense\ValueObjects\LicenseKey;
use Akira\LaravelLicense\ValueObjects\MachineFingerprint;

test('throws exception when license is not loaded', function () {
    $stage = new MachineCheckStage();
    $context = new LicenseContext(
        key: LicenseKey::fromString('AAAA-BBBB-CCCC-DDDD'),
        domain: null,
        machineFingerprint: MachineFingerprint::fromRaw('machine-123'),
    );

    $stage($context);
})->throws(LicenseNotLoadedException::class);

test('returns context when machine fingerprint is not provided', function () {
    $stage = new MachineCheckStage();
    $license = License::factory()->create();
    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        machineFingerprint: null,
        license: $license,
    );

    $result = $stage($context);

    expect($result)->toBe($context);
});

test('returns context when machine already has activation', function () {
    $stage = new MachineCheckStage();
    $license = License::factory()->create(['max_activations' => 5]);
    $machine = MachineFingerprint::fromRaw('machine-123');

    LicenseActivation::factory()->create([
        'license_id' => $license->id,
        'machine_hash' => $machine->hash,
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        machineFingerprint: $machine,
        license: $license,
    );

    $result = $stage($context);

    expect($result)->toBe($context);
});

test('returns context when activation limit not reached', function () {
    $stage = new MachineCheckStage();
    $license = License::factory()->create(['max_activations' => 5]);
    $machine = MachineFingerprint::fromRaw('new-machine');

    LicenseActivation::factory()->count(3)->create([
        'license_id' => $license->id,
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        machineFingerprint: $machine,
        license: $license,
    );

    $result = $stage($context);

    expect($result)->toBe($context);
});

test('throws exception when activation limit reached', function () {
    $stage = new MachineCheckStage();
    $license = License::factory()->create(['max_activations' => 3]);
    $machine = MachineFingerprint::fromRaw('new-machine');

    LicenseActivation::factory()->count(3)->create([
        'license_id' => $license->id,
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        machineFingerprint: $machine,
        license: $license,
    );

    $stage($context);
})->throws(ActivationLimitReachedException::class);

test('allows activation when exactly at limit but machine exists', function () {
    $stage = new MachineCheckStage();
    $license = License::factory()->create(['max_activations' => 3]);
    $machine = MachineFingerprint::fromRaw('existing-machine');

    LicenseActivation::factory()->count(2)->create([
        'license_id' => $license->id,
    ]);

    LicenseActivation::factory()->create([
        'license_id' => $license->id,
        'machine_hash' => $machine->hash,
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        machineFingerprint: $machine,
        license: $license,
    );

    $result = $stage($context);

    expect($result)->toBe($context);
});
