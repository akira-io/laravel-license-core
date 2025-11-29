<?php

declare(strict_types=1);

use Akira\LaravelLicense\Enums\LicenseStatus;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\ValueObjects\LicenseData;

it('can create license data with all properties', function () {
    $expiresAt = now()->addYear();
    $graceEndsAt = now()->addYear()->addDays(7);

    $data = new LicenseData(
        key: 'TEST-KEY-1234',
        type: LicenseType::ANNUAL,
        status: LicenseStatus::ACTIVE,
        maxActivations: 5,
        maxSeats: 10,
        fallback: false,
        scopes: ['feature:advanced'],
        meta: ['client' => 'Test Corp'],
        expiresAt: $expiresAt,
        graceEndsAt: $graceEndsAt,
    );

    expect($data->key)->toBe('TEST-KEY-1234')
        ->and($data->type)->toBe(LicenseType::ANNUAL)
        ->and($data->status)->toBe(LicenseStatus::ACTIVE)
        ->and($data->maxActivations)->toBe(5)
        ->and($data->maxSeats)->toBe(10)
        ->and($data->fallback)->toBeFalse()
        ->and($data->scopes)->toBe(['feature:advanced'])
        ->and($data->meta)->toBe(['client' => 'Test Corp'])
        ->and($data->expiresAt)->toBe($expiresAt)
        ->and($data->graceEndsAt)->toBe($graceEndsAt);
});

it('converts to array correctly', function () {
    $expiresAt = now()->addYear();
    $graceEndsAt = now()->addYear()->addDays(7);

    $data = new LicenseData(
        key: 'TEST-KEY-1234',
        type: LicenseType::SUBSCRIPTION,
        status: LicenseStatus::ACTIVE,
        maxActivations: 3,
        maxSeats: 5,
        fallback: true,
        scopes: ['feature:api'],
        meta: ['tier' => 'premium'],
        expiresAt: $expiresAt,
        graceEndsAt: $graceEndsAt,
    );

    $array = $data->toArray();

    expect($array)->toBeArray()
        ->and($array['key'])->toBe('TEST-KEY-1234')
        ->and($array['type'])->toBe(LicenseType::SUBSCRIPTION->value)
        ->and($array['status'])->toBe(LicenseStatus::ACTIVE->value)
        ->and($array['max_activations'])->toBe(3)
        ->and($array['max_seats'])->toBe(5)
        ->and($array['fallback'])->toBeTrue()
        ->and($array['scopes'])->toBe(['feature:api'])
        ->and($array['meta'])->toBe(['tier' => 'premium'])
        ->and($array['expires_at'])->toBe($expiresAt)
        ->and($array['grace_ends_at'])->toBe($graceEndsAt);
});

it('can create from array', function () {
    $expiresAt = now()->addMonth();

    $array = [
        'key' => 'FROM-ARRAY-KEY',
        'type' => LicenseType::TRIAL->value,
        'status' => LicenseStatus::ACTIVE->value,
        'max_activations' => 1,
        'max_seats' => 1,
        'fallback' => false,
        'scopes' => ['feature:trial'],
        'meta' => ['source' => 'array'],
        'expires_at' => $expiresAt,
        'grace_ends_at' => null,
    ];

    $data = LicenseData::fromArray($array);

    expect($data)->toBeInstanceOf(LicenseData::class)
        ->and($data->key)->toBe('FROM-ARRAY-KEY')
        ->and($data->type)->toBe(LicenseType::TRIAL)
        ->and($data->status)->toBe(LicenseStatus::ACTIVE)
        ->and($data->maxActivations)->toBe(1)
        ->and($data->maxSeats)->toBe(1)
        ->and($data->fallback)->toBeFalse()
        ->and($data->scopes)->toBe(['feature:trial'])
        ->and($data->meta)->toBe(['source' => 'array'])
        ->and($data->expiresAt)->toBe($expiresAt)
        ->and($data->graceEndsAt)->toBeNull();
});

it('can create from array with enum instances', function () {
    $array = [
        'key' => 'ENUM-TEST-KEY',
        'type' => LicenseType::LIFETIME,
        'status' => LicenseStatus::SUSPENDED,
        'max_activations' => 10,
        'max_seats' => 10,
        'fallback' => true,
    ];

    $data = LicenseData::fromArray($array);

    expect($data->type)->toBe(LicenseType::LIFETIME)
        ->and($data->status)->toBe(LicenseStatus::SUSPENDED);
});

it('handles null scopes and meta', function () {
    $data = new LicenseData(
        key: 'NULL-DATA-KEY',
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

    expect($data->scopes)->toBeNull()
        ->and($data->meta)->toBeNull()
        ->and($data->expiresAt)->toBeNull()
        ->and($data->graceEndsAt)->toBeNull();
});

it('converts null values correctly in toArray', function () {
    $data = new LicenseData(
        key: 'NULL-ARRAY-KEY',
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

    $array = $data->toArray();

    expect($array['scopes'])->toBeNull()
        ->and($array['meta'])->toBeNull()
        ->and($array['expires_at'])->toBeNull()
        ->and($array['grace_ends_at'])->toBeNull();
});

it('is readonly', function () {
    $data = new LicenseData(
        key: 'READONLY-TEST',
        type: LicenseType::ANNUAL,
        status: LicenseStatus::ACTIVE,
        maxActivations: 5,
        maxSeats: 10,
        fallback: false,
        scopes: null,
        meta: null,
        expiresAt: null,
        graceEndsAt: null,
    );

    expect(fn () => $data->key = 'NEW-KEY')
        ->toThrow(Error::class);
});

it('is final class', function () {
    $reflection = new ReflectionClass(LicenseData::class);

    expect($reflection->isFinal())->toBeTrue();
});

it('handles different license types', function () {
    $types = [
        LicenseType::LIFETIME,
        LicenseType::ANNUAL,
        LicenseType::SUBSCRIPTION,
        LicenseType::TRIAL,
        LicenseType::CREDITS,
    ];

    foreach ($types as $type) {
        $data = new LicenseData(
            key: "KEY-{$type->value}",
            type: $type,
            status: LicenseStatus::ACTIVE,
            maxActivations: 1,
            maxSeats: 1,
            fallback: false,
            scopes: null,
            meta: null,
            expiresAt: null,
            graceEndsAt: null,
        );

        expect($data->type)->toBe($type);
    }
});

it('handles different license statuses', function () {
    $statuses = [
        LicenseStatus::ACTIVE,
        LicenseStatus::EXPIRED,
        LicenseStatus::SUSPENDED,
        LicenseStatus::REVOKED,
    ];

    foreach ($statuses as $status) {
        $data = new LicenseData(
            key: "KEY-{$status->value}",
            type: LicenseType::LIFETIME,
            status: $status,
            maxActivations: 1,
            maxSeats: 1,
            fallback: false,
            scopes: null,
            meta: null,
            expiresAt: null,
            graceEndsAt: null,
        );

        expect($data->status)->toBe($status);
    }
});

it('can store complex scopes array', function () {
    $scopes = [
        'feature:advanced',
        'feature:api',
        'feature:export',
        'feature:import',
        'module:billing',
        'module:reporting',
    ];

    $data = new LicenseData(
        key: 'SCOPES-KEY',
        type: LicenseType::SUBSCRIPTION,
        status: LicenseStatus::ACTIVE,
        maxActivations: 10,
        maxSeats: 20,
        fallback: false,
        scopes: $scopes,
        meta: null,
        expiresAt: now()->addMonth(),
        graceEndsAt: null,
    );

    expect($data->scopes)->toBeArray()
        ->and($data->scopes)->toHaveCount(6);
});

it('can store complex meta array', function () {
    $meta = [
        'client_name' => 'Acme Corporation',
        'purchase_order' => 'PO-2024-001',
        'billing_email' => 'billing@acme.com',
        'custom_fields' => [
            'department' => 'IT',
            'cost_center' => 'CC-1234',
        ],
    ];

    $data = new LicenseData(
        key: 'META-KEY',
        type: LicenseType::ANNUAL,
        status: LicenseStatus::ACTIVE,
        maxActivations: 5,
        maxSeats: 10,
        fallback: false,
        scopes: null,
        meta: $meta,
        expiresAt: now()->addYear(),
        graceEndsAt: null,
    );

    expect($data->meta)->toBeArray()
        ->and($data->meta['client_name'])->toBe('Acme Corporation')
        ->and($data->meta['custom_fields'])->toBeArray();
});

it('preserves dates when converting to array and back', function () {
    $expiresAt = now()->addYear();
    $graceEndsAt = now()->addYear()->addWeek();

    $data = new LicenseData(
        key: 'DATE-TEST-KEY',
        type: LicenseType::ANNUAL,
        status: LicenseStatus::ACTIVE,
        maxActivations: 5,
        maxSeats: 10,
        fallback: false,
        scopes: null,
        meta: null,
        expiresAt: $expiresAt,
        graceEndsAt: $graceEndsAt,
    );

    $array = $data->toArray();
    $reconstructed = LicenseData::fromArray($array);

    expect($reconstructed->expiresAt)->toBe($expiresAt)
        ->and($reconstructed->graceEndsAt)->toBe($graceEndsAt);
});
