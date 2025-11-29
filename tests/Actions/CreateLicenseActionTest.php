<?php

declare(strict_types=1);

use Akira\LaravelLicense\Actions\CreateLicenseAction;
use Akira\LaravelLicense\Enums\LicenseStatus;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Support\ConfigManager;
use Akira\LaravelLicense\Support\KeyGenerator;
use Akira\LaravelLicense\ValueObjects\LicenseData;

it('creates a license with provided data', function () {
    $keyGenerator = resolve(KeyGenerator::class);
    $configManager = resolve(ConfigManager::class);

    $data = new LicenseData(
        key: 'TEST-KEY-1234',
        type: LicenseType::ANNUAL,
        status: LicenseStatus::ACTIVE,
        maxActivations: 5,
        maxSeats: 10,
        fallback: false,
        scopes: ['feature:advanced'],
        meta: ['client' => 'Test Corp'],
        expiresAt: now()->addYear(),
        graceEndsAt: now()->addYear()->addDays(7),
    );

    $action = new CreateLicenseAction($keyGenerator, $configManager);
    $license = $action->handle($data);

    expect($license)->toBeInstanceOf(License::class)
        ->and($license->key)->toBe('TEST-KEY-1234')
        ->and($license->type)->toBe(LicenseType::ANNUAL->value)
        ->and($license->status)->toBe(LicenseStatus::ACTIVE->value)
        ->and($license->max_activations)->toBe(5)
        ->and($license->max_seats)->toBe(10)
        ->and($license->fallback)->toBeFalse();
});

it('creates a license with auto-generated key', function () {
    $keyGenerator = resolve(KeyGenerator::class);
    $configManager = resolve(ConfigManager::class);

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

    $action = new CreateLicenseAction($keyGenerator, $configManager);
    $license = $action->handleWithAutoKey($data);

    expect($license)->toBeInstanceOf(License::class)
        ->and($license->key)->not->toBeEmpty()
        ->and($license->type)->toBe(LicenseType::LIFETIME->value);
});

it('stores scopes correctly', function () {
    $keyGenerator = resolve(KeyGenerator::class);
    $configManager = resolve(ConfigManager::class);

    $data = new LicenseData(
        key: 'TEST-KEY-SCOPES',
        type: LicenseType::SUBSCRIPTION,
        status: LicenseStatus::ACTIVE,
        maxActivations: 5,
        maxSeats: 10,
        fallback: false,
        scopes: ['feature:api', 'feature:export', 'feature:advanced'],
        meta: null,
        expiresAt: now()->addMonth(),
        graceEndsAt: null,
    );

    $action = new CreateLicenseAction($keyGenerator, $configManager);
    $license = $action->handle($data);

    expect($license->scopes)->toHaveCount(3)
        ->and($license->scopes[0])->toBe('feature:api')
        ->and($license->scopes[1])->toBe('feature:export')
        ->and($license->scopes[2])->toBe('feature:advanced');
});

it('stores encrypted meta data', function () {
    $keyGenerator = resolve(KeyGenerator::class);
    $configManager = resolve(ConfigManager::class);

    $metaData = [
        'client_name' => 'Acme Corp',
        'purchase_order' => 'PO-12345',
        'custom_field' => 'custom_value',
    ];

    $data = new LicenseData(
        key: 'TEST-KEY-META',
        type: LicenseType::ANNUAL,
        status: LicenseStatus::ACTIVE,
        maxActivations: 5,
        maxSeats: 10,
        fallback: false,
        scopes: null,
        meta: $metaData,
        expiresAt: now()->addYear(),
        graceEndsAt: null,
    );

    $action = new CreateLicenseAction($keyGenerator, $configManager);
    $license = $action->handle($data);

    expect($license->meta)->toBeArray()
        ->and($license->meta['client_name'])->toBe('Acme Corp')
        ->and($license->meta['purchase_order'])->toBe('PO-12345');
});

it('creates license in database', function () {
    $keyGenerator = resolve(KeyGenerator::class);
    $configManager = resolve(ConfigManager::class);

    $data = new LicenseData(
        key: 'TEST-DB-KEY-1234',
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

    $action = new CreateLicenseAction($keyGenerator, $configManager);
    $license = $action->handle($data);

    $this->assertDatabaseHas('licenses', [
        'id' => $license->id,
        'key' => 'TEST-DB-KEY-1234',
        'type' => LicenseType::TRIAL->value,
    ]);
});

it('is readonly class', function () {
    $reflection = new ReflectionClass(CreateLicenseAction::class);

    expect($reflection->isReadOnly())->toBeTrue();
});

it('is final class', function () {
    $reflection = new ReflectionClass(CreateLicenseAction::class);

    expect($reflection->isFinal())->toBeTrue();
});

it('creates license with null dates', function () {
    $keyGenerator = resolve(KeyGenerator::class);
    $configManager = resolve(ConfigManager::class);

    $data = new LicenseData(
        key: 'TEST-NULL-DATES',
        type: LicenseType::LIFETIME,
        status: LicenseStatus::ACTIVE,
        maxActivations: 999,
        maxSeats: 999,
        fallback: false,
        scopes: null,
        meta: null,
        expiresAt: null,
        graceEndsAt: null,
    );

    $action = new CreateLicenseAction($keyGenerator, $configManager);
    $license = $action->handle($data);

    expect($license->expires_at)->toBeNull()
        ->and($license->grace_ends_at)->toBeNull();
});

it('creates license with fallback enabled', function () {
    $keyGenerator = resolve(KeyGenerator::class);
    $configManager = resolve(ConfigManager::class);

    $data = new LicenseData(
        key: 'TEST-FALLBACK-KEY',
        type: LicenseType::SUBSCRIPTION,
        status: LicenseStatus::ACTIVE,
        maxActivations: 5,
        maxSeats: 10,
        fallback: true,
        scopes: null,
        meta: null,
        expiresAt: now()->addMonth(),
        graceEndsAt: null,
    );

    $action = new CreateLicenseAction($keyGenerator, $configManager);
    $license = $action->handle($data);

    expect($license->fallback)->toBeTrue();
});
