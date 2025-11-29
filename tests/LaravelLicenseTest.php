<?php

declare(strict_types=1);

use Akira\LaravelLicense\Enums\LicenseStatus;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\LaravelLicense;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseUsage;
use Akira\LaravelLicense\ValueObjects\LicenseData;

test('validate usage returns true for valid license', function () {
    $license = License::factory()->create(['status' => 'active']);

    $service = app(LaravelLicense::class);
    $result = $service->validateUsage(
        key: $license->key,
        machine: 'test-machine-123',
        domain: 'example.com',
        activate: true
    );

    expect($result)->toBeTrue();
});

test('validate usage returns false for invalid license', function () {
    $service = app(LaravelLicense::class);
    $result = $service->validateUsage(
        key: 'INVALID-KEY',
        machine: 'test-machine-123',
        domain: 'example.com',
        activate: true
    );

    expect($result)->toBeFalse();
});

test('validate usage without activation', function () {
    $license = License::factory()->create(['status' => 'active']);

    $service = app(LaravelLicense::class);
    $result = $service->validateUsage(
        key: $license->key,
        machine: 'test-machine-123',
        domain: 'example.com',
        activate: false
    );

    expect($result)->toBeTrue();
});

test('validate usage without domain', function () {
    $license = License::factory()->create(['status' => 'active']);

    $service = app(LaravelLicense::class);
    $result = $service->validateUsage(
        key: $license->key,
        machine: 'test-machine-123',
        domain: null,
        activate: true
    );

    expect($result)->toBeTrue();
});

test('validate usage returns false for empty machine', function () {
    $license = License::factory()->create(['status' => 'active']);

    $service = app(LaravelLicense::class);
    $result = $service->validateUsage(
        key: $license->key,
        machine: '',
        domain: 'example.com',
        activate: true
    );

    expect($result)->toBeFalse();
});

test('validate update returns true for valid subscription', function () {
    $expiryDate = now()->addYear();
    $license = License::factory()->create([
        'status' => 'active',
        'type' => LicenseType::SUBSCRIPTION->value,
        'expires_at' => $expiryDate,
    ]);

    $service = app(LaravelLicense::class);
    $result = $service->validateUpdate(
        key: $license->key,
        releaseDate: now()->subMonth(),
        domain: 'example.com',
        machine: 'test-machine'
    );

    expect($result)->toBeTrue();
});

test('validate update returns false for invalid license', function () {
    $service = app(LaravelLicense::class);
    $result = $service->validateUpdate(
        key: 'INVALID-KEY',
        releaseDate: now(),
        domain: 'example.com',
        machine: 'test-machine'
    );

    expect($result)->toBeFalse();
});

test('validate update without domain and machine', function () {
    $expiryDate = now()->addYear();
    $license = License::factory()->create([
        'status' => 'active',
        'type' => LicenseType::SUBSCRIPTION->value,
        'expires_at' => $expiryDate,
    ]);

    $service = app(LaravelLicense::class);
    $result = $service->validateUpdate(
        key: $license->key,
        releaseDate: now()->subMonth(),
        domain: null,
        machine: null
    );

    expect($result)->toBeTrue();
});

test('consume credits returns true when successful', function () {
    $license = License::factory()->create([
        'type' => LicenseType::CREDITS->value,
        'status' => 'active',
    ]);

    LicenseUsage::factory()->create([
        'license_id' => $license->id,
        'limit' => 100,
        'consumed_units' => 50,
    ]);

    $service = app(LaravelLicense::class);
    $result = $service->consumeCredits(
        key: $license->key,
        amount: 10
    );

    expect($result)->toBeTrue();
});

test('consume credits returns false for invalid license', function () {
    $service = app(LaravelLicense::class);
    $result = $service->consumeCredits(
        key: 'INVALID-KEY',
        amount: 10
    );

    expect($result)->toBeFalse();
});

test('consume credits returns false when insufficient credits', function () {
    $license = License::factory()->create([
        'type' => LicenseType::CREDITS->value,
        'status' => 'active',
    ]);

    LicenseUsage::factory()->create([
        'license_id' => $license->id,
        'limit' => 100,
        'consumed_units' => 95,
    ]);

    $service = app(LaravelLicense::class);
    $result = $service->consumeCredits(
        key: $license->key,
        amount: 10
    );

    expect($result)->toBeFalse();
});

test('rotate key returns new key when successful', function () {
    $license = License::factory()->create(['status' => 'active']);
    $oldKey = $license->key;

    $service = app(LaravelLicense::class);
    $newKey = $service->rotateKey($oldKey);

    expect($newKey)->not->toBeNull()
        ->and($newKey)->not->toBe($oldKey);
});

test('rotate key returns null for invalid license', function () {
    $service = app(LaravelLicense::class);
    $result = $service->rotateKey('INVALID-KEY');

    expect($result)->toBeNull();
});

test('create license via facade', function () {
    $service = app(LaravelLicense::class);

    $data = new LicenseData(
        key: 'TEST-FACADE-KEY',
        type: LicenseType::ANNUAL,
        status: LicenseStatus::ACTIVE,
        maxActivations: 5,
        maxSeats: 10,
        fallback: false,
        scopes: ['feature:test'],
        meta: ['created_via' => 'facade'],
        expiresAt: now()->addYear(),
        graceEndsAt: null,
    );

    $license = $service->create($data);

    expect($license)->toBeInstanceOf(License::class)
        ->and($license->key)->toBe('TEST-FACADE-KEY')
        ->and($license->type)->toBe(LicenseType::ANNUAL->value);
});

test('create license with auto key via facade', function () {
    $service = app(LaravelLicense::class);

    $data = new LicenseData(
        key: '',
        type: LicenseType::LIFETIME,
        status: LicenseStatus::ACTIVE,
        maxActivations: 1,
        maxSeats: 1,
        fallback: false,
        scopes: null,
        meta: null,
        expiresAt: null,
        graceEndsAt: null,
    );

    $license = $service->createWithAutoKey($data);

    expect($license)->toBeInstanceOf(License::class)
        ->and($license->key)->not->toBeEmpty()
        ->and($license->type)->toBe(LicenseType::LIFETIME->value);
});

test('update license via facade', function () {
    $license = License::factory()->create([
        'key' => 'OLD-FACADE-KEY',
        'max_activations' => 5,
    ]);

    $service = app(LaravelLicense::class);

    $data = new LicenseData(
        key: 'OLD-FACADE-KEY',
        type: LicenseType::from($license->type),
        status: LicenseStatus::SUSPENDED,
        maxActivations: 15,
        maxSeats: $license->max_seats,
        fallback: $license->fallback,
        scopes: null,
        meta: null,
        expiresAt: $license->expires_at,
        graceEndsAt: $license->grace_ends_at,
    );

    $updated = $service->update($license, $data);

    expect($updated->status)->toBe(LicenseStatus::SUSPENDED->value)
        ->and($updated->max_activations)->toBe(15);
});

test('update license by key via facade', function () {
    $license = License::factory()->create([
        'key' => 'UPDATE-BY-KEY',
        'max_activations' => 5,
    ]);

    $service = app(LaravelLicense::class);

    $data = new LicenseData(
        key: 'UPDATE-BY-KEY',
        type: LicenseType::from($license->type),
        status: LicenseStatus::from($license->status),
        maxActivations: 20,
        maxSeats: $license->max_seats,
        fallback: $license->fallback,
        scopes: null,
        meta: null,
        expiresAt: $license->expires_at,
        graceEndsAt: $license->grace_ends_at,
    );

    $updated = $service->updateByKey('UPDATE-BY-KEY', $data);

    expect($updated)->not->toBeNull()
        ->and($updated->max_activations)->toBe(20);
});

test('update license by key returns null for invalid key', function () {
    $service = app(LaravelLicense::class);

    $data = new LicenseData(
        key: 'INVALID-KEY',
        type: LicenseType::LIFETIME,
        status: LicenseStatus::ACTIVE,
        maxActivations: 1,
        maxSeats: 1,
        fallback: false,
        scopes: null,
        meta: null,
        expiresAt: null,
        graceEndsAt: null,
    );

    $result = $service->updateByKey('INVALID-KEY', $data);

    expect($result)->toBeNull();
});
