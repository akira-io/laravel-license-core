<?php

declare(strict_types=1);

use Akira\LaravelLicense\Enums\LicenseStatus;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Exceptions\LicenseExpiredException;
use Akira\LaravelLicense\Exceptions\LicenseNotLoadedException;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Pipelines\Stages\ExpirationUsageStage;
use Akira\LaravelLicense\ValueObjects\LicenseContext;
use Akira\LaravelLicense\ValueObjects\LicenseKey;

it('throws exception when license is not loaded', function () {
    $stage = new ExpirationUsageStage();
    $context = new LicenseContext(
        key: LicenseKey::fromString('TEST-KEY-1234'),
        domain: null
    );

    $stage($context);
})->throws(LicenseNotLoadedException::class);

it('allows lifetime licenses without expiration check', function () {
    $stage = new ExpirationUsageStage();

    $license = License::factory()->create([
        'type' => LicenseType::LIFETIME->value,
        'status' => LicenseStatus::ACTIVE->value,
        'expires_at' => null,
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license
    );

    $result = $stage($context);

    expect($result)->toBe($context);
});

it('allows credits licenses without expiration check', function () {
    $stage = new ExpirationUsageStage();

    $license = License::factory()->create([
        'type' => LicenseType::CREDITS->value,
        'status' => LicenseStatus::ACTIVE->value,
        'expires_at' => now()->addYear(),
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license
    );

    $result = $stage($context);

    expect($result)->toBe($context);
});

it('allows non-expired annual licenses', function () {
    $stage = new ExpirationUsageStage();

    $license = License::factory()->create([
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
        'expires_at' => now()->addMonths(6),
        'fallback' => false,
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license
    );

    $result = $stage($context);

    expect($result)->toBe($context);
});

it('throws exception for expired annual license without fallback', function () {
    $stage = new ExpirationUsageStage();

    $license = License::factory()->create([
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
        'expires_at' => now()->subDays(1),
        'fallback' => false,
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license
    );

    $stage($context);
})->throws(LicenseExpiredException::class);

it('allows expired annual license with fallback', function () {
    $stage = new ExpirationUsageStage();

    $license = License::factory()->create([
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
        'expires_at' => now()->subDays(1),
        'fallback' => true,
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license
    );

    $result = $stage($context);

    expect($result)->toBe($context);
});

it('allows expired subscription license within grace period', function () {
    $stage = new ExpirationUsageStage();

    $license = License::factory()->create([
        'type' => LicenseType::SUBSCRIPTION->value,
        'status' => LicenseStatus::ACTIVE->value,
        'expires_at' => now()->subDays(5),
        'grace_ends_at' => now()->addDays(9), // 14 days from expiration
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license
    );

    $result = $stage($context);

    expect($result)->toBe($context);
});

it('throws exception for expired subscription license outside grace period', function () {
    $stage = new ExpirationUsageStage();

    $license = License::factory()->create([
        'type' => LicenseType::SUBSCRIPTION->value,
        'status' => LicenseStatus::ACTIVE->value,
        'expires_at' => now()->subDays(20),
        'grace_ends_at' => now()->subDays(6), // grace period ended 6 days ago
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license
    );

    $stage($context);
})->throws(LicenseExpiredException::class);

it('allows non-expired subscription license', function () {
    $stage = new ExpirationUsageStage();

    $license = License::factory()->create([
        'type' => LicenseType::SUBSCRIPTION->value,
        'status' => LicenseStatus::ACTIVE->value,
        'expires_at' => now()->addMonth(),
        'grace_ends_at' => now()->addMonth()->addDays(14),
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license
    );

    $result = $stage($context);

    expect($result)->toBe($context);
});
