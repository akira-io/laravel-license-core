<?php

declare(strict_types=1);

use Akira\LaravelLicense\Actions\UpdateLicenseAction;
use Akira\LaravelLicense\Enums\LicenseStatus;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\ValueObjects\LicenseData;

it('updates a license with new data', function () {
    $license = License::factory()->create([
        'key' => 'OLD-KEY-1234',
        'type' => LicenseType::TRIAL->value,
        'status' => LicenseStatus::ACTIVE->value,
        'max_activations' => 1,
        'max_seats' => 1,
    ]);

    $data = new LicenseData(
        key: 'NEW-KEY-5678',
        type: LicenseType::ANNUAL,
        status: LicenseStatus::SUSPENDED,
        maxActivations: 10,
        maxSeats: 20,
        fallback: true,
        scopes: ['feature:premium'],
        meta: ['updated' => true],
        expiresAt: now()->addYear(),
        graceEndsAt: now()->addYear()->addDays(7),
    );

    $action = new UpdateLicenseAction();
    $updated = $action->handle($license, $data);

    expect($updated->key)->toBe('NEW-KEY-5678')
        ->and($updated->type)->toBe(LicenseType::ANNUAL->value)
        ->and($updated->status)->toBe(LicenseStatus::SUSPENDED->value)
        ->and($updated->max_activations)->toBe(10)
        ->and($updated->max_seats)->toBe(20)
        ->and($updated->fallback)->toBeTrue();
});

it('updates license status', function () {
    $license = License::factory()->active()->create();

    $data = new LicenseData(
        key: $license->key,
        type: LicenseType::from($license->type),
        status: LicenseStatus::SUSPENDED,
        maxActivations: $license->max_activations,
        maxSeats: $license->max_seats,
        fallback: $license->fallback,
        scopes: $license->scopes?->getArrayCopy(),
        meta: $license->meta,
        expiresAt: $license->expires_at,
        graceEndsAt: $license->grace_ends_at,
    );

    $action = new UpdateLicenseAction();
    $updated = $action->handle($license, $data);

    expect($updated->status)->toBe(LicenseStatus::SUSPENDED->value);
});

it('updates license scopes', function () {
    $license = License::factory()->create([
        'scopes' => ['feature:basic'],
    ]);

    $data = new LicenseData(
        key: $license->key,
        type: LicenseType::from($license->type),
        status: LicenseStatus::from($license->status),
        maxActivations: $license->max_activations,
        maxSeats: $license->max_seats,
        fallback: $license->fallback,
        scopes: ['feature:advanced', 'feature:api'],
        meta: $license->meta,
        expiresAt: $license->expires_at,
        graceEndsAt: $license->grace_ends_at,
    );

    $action = new UpdateLicenseAction();
    $updated = $action->handle($license, $data);

    expect($updated->scopes)->toHaveCount(2);
});

it('updates license meta data', function () {
    $license = License::factory()->create([
        'meta' => ['old' => 'value'],
    ]);

    $data = new LicenseData(
        key: $license->key,
        type: LicenseType::from($license->type),
        status: LicenseStatus::from($license->status),
        maxActivations: $license->max_activations,
        maxSeats: $license->max_seats,
        fallback: $license->fallback,
        scopes: $license->scopes?->getArrayCopy(),
        meta: ['new' => 'data', 'additional' => 'info'],
        expiresAt: $license->expires_at,
        graceEndsAt: $license->grace_ends_at,
    );

    $action = new UpdateLicenseAction();
    $updated = $action->handle($license, $data);

    expect($updated->meta)->toHaveKey('new')
        ->and($updated->meta['new'])->toBe('data')
        ->and($updated->meta)->toHaveKey('additional');
});

it('updates expiration dates', function () {
    $license = License::factory()->create([
        'expires_at' => now()->addDays(7),
        'grace_ends_at' => null,
    ]);

    $newExpiration = now()->addYear();
    $newGraceEnd = now()->addYear()->addDays(30);

    $data = new LicenseData(
        key: $license->key,
        type: LicenseType::from($license->type),
        status: LicenseStatus::from($license->status),
        maxActivations: $license->max_activations,
        maxSeats: $license->max_seats,
        fallback: $license->fallback,
        scopes: $license->scopes?->getArrayCopy(),
        meta: $license->meta,
        expiresAt: $newExpiration,
        graceEndsAt: $newGraceEnd,
    );

    $action = new UpdateLicenseAction();
    $updated = $action->handle($license, $data);

    expect($updated->expires_at->toDateString())->toBe($newExpiration->toDateString())
        ->and($updated->grace_ends_at->toDateString())->toBe($newGraceEnd->toDateString());
});

it('persists changes to database', function () {
    $license = License::factory()->create([
        'key' => 'ORIGINAL-KEY',
        'max_activations' => 5,
    ]);

    $data = new LicenseData(
        key: 'UPDATED-KEY-DB',
        type: LicenseType::from($license->type),
        status: LicenseStatus::from($license->status),
        maxActivations: 15,
        maxSeats: $license->max_seats,
        fallback: $license->fallback,
        scopes: $license->scopes?->getArrayCopy(),
        meta: $license->meta,
        expiresAt: $license->expires_at,
        graceEndsAt: $license->grace_ends_at,
    );

    $action = new UpdateLicenseAction();
    $action->handle($license, $data);

    $this->assertDatabaseHas('licenses', [
        'id' => $license->id,
        'key' => 'UPDATED-KEY-DB',
        'max_activations' => 15,
    ]);
});

it('returns fresh license instance', function () {
    $license = License::factory()->create();

    $data = new LicenseData(
        key: 'FRESH-KEY-TEST',
        type: LicenseType::from($license->type),
        status: LicenseStatus::from($license->status),
        maxActivations: $license->max_activations,
        maxSeats: $license->max_seats,
        fallback: $license->fallback,
        scopes: $license->scopes?->getArrayCopy(),
        meta: $license->meta,
        expiresAt: $license->expires_at,
        graceEndsAt: $license->grace_ends_at,
    );

    $action = new UpdateLicenseAction();
    $updated = $action->handle($license, $data);

    expect($updated)->toBeInstanceOf(License::class)
        ->and($updated->key)->toBe('FRESH-KEY-TEST')
        ->and($updated->exists)->toBeTrue();
});

it('is readonly class', function () {
    $reflection = new ReflectionClass(UpdateLicenseAction::class);

    expect($reflection->isReadOnly())->toBeTrue();
});

it('is final class', function () {
    $reflection = new ReflectionClass(UpdateLicenseAction::class);

    expect($reflection->isFinal())->toBeTrue();
});
