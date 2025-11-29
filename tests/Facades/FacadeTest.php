<?php

declare(strict_types=1);

use Akira\LaravelLicense\Enums\LicenseStatus;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Facades\License;
use Akira\LaravelLicense\LaravelLicense;
use Akira\LaravelLicense\ValueObjects\LicenseData;

it('resolves facade to correct class instance', function () {
    $instance = License::getFacadeRoot();

    expect($instance)->toBeInstanceOf(LaravelLicense::class);
});

it('facade is registered in service container', function () {
    $instance = app(LaravelLicense::class);

    expect($instance)->toBeInstanceOf(LaravelLicense::class);
});

it('has protected facade accessor method', function () {
    $reflection = new ReflectionClass(License::class);
    $method = $reflection->getMethod('getFacadeAccessor');

    expect($method->isProtected())->toBeTrue()
        ->and($method->isStatic())->toBeTrue();
});

it('facade accessor returns correct class name', function () {
    $reflection = new ReflectionClass(License::class);
    $method = $reflection->getMethod('getFacadeAccessor');
    $method->setAccessible(true);

    $accessor = $method->invoke(null);

    expect($accessor)->toBe(LaravelLicense::class);
});

it('can create license via static facade', function () {
    $data = new LicenseData(
        key: 'STATIC-FACADE-KEY',
        type: LicenseType::ANNUAL,
        status: LicenseStatus::ACTIVE,
        maxActivations: 5,
        maxSeats: 10,
        fallback: false,
        scopes: null,
        meta: null,
        expiresAt: now()->addYear(),
        graceEndsAt: null,
    );

    $license = License::createLicense($data);

    expect($license)->toBeInstanceOf(Akira\LaravelLicense\Models\License::class)
        ->and($license->key)->toBe('STATIC-FACADE-KEY');
});

it('can create license with auto key via static facade', function () {
    $data = new LicenseData(
        key: '',
        type: LicenseType::TRIAL,
        status: LicenseStatus::ACTIVE,
        maxActivations: 1,
        maxSeats: 1,
        fallback: false,
        scopes: null,
        meta: null,
        expiresAt: now()->addDays(14),
        graceEndsAt: null,
    );

    $license = License::createLicenseWithAutoKey($data);

    expect($license)->toBeInstanceOf(Akira\LaravelLicense\Models\License::class)
        ->and($license->key)->not->toBeEmpty();
});

it('can update license via static facade', function () {
    $license = Akira\LaravelLicense\Models\License::factory()->create([
        'max_activations' => 5,
    ]);

    $data = new LicenseData(
        key: $license->key,
        type: LicenseType::from($license->type),
        status: LicenseStatus::from($license->status),
        maxActivations: 10,
        maxSeats: $license->max_seats,
        fallback: $license->fallback,
        scopes: null,
        meta: null,
        expiresAt: $license->expires_at,
        graceEndsAt: $license->grace_ends_at,
    );

    $updated = License::updateLicense($license, $data);

    expect($updated->max_activations)->toBe(10);
});

it('can update license by key via static facade', function () {
    $license = Akira\LaravelLicense\Models\License::factory()->create([
        'key' => 'FACADE-UPDATE-KEY',
        'max_activations' => 3,
    ]);

    $data = new LicenseData(
        key: 'FACADE-UPDATE-KEY',
        type: LicenseType::from($license->type),
        status: LicenseStatus::from($license->status),
        maxActivations: 8,
        maxSeats: $license->max_seats,
        fallback: $license->fallback,
        scopes: null,
        meta: null,
        expiresAt: $license->expires_at,
        graceEndsAt: $license->grace_ends_at,
    );

    $updated = License::updateLicenseByKey('FACADE-UPDATE-KEY', $data);

    expect($updated)->not->toBeNull()
        ->and($updated->max_activations)->toBe(8);
});
