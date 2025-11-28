<?php

declare(strict_types=1);

use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\LaravelLicense;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseUsage;

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
