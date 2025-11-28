<?php

declare(strict_types=1);

use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Exceptions\LicenseExpiredException;
use Akira\LaravelLicense\Exceptions\LicenseNotLoadedException;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Pipelines\Stages\GracePeriodStage;
use Akira\LaravelLicense\Support\ConfigManager;
use Akira\LaravelLicense\ValueObjects\LicenseContext;
use Akira\LaravelLicense\ValueObjects\LicenseKey;

test('allows lifetime license', function () {
    $configManager = resolve(ConfigManager::class);
    $stage = new GracePeriodStage($configManager->getGracePeriod());

    $license = License::factory()->create([
        'type' => LicenseType::LIFETIME->value,
        'expires_at' => now()->subDay(),
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license,
    );

    $result = $stage($context);

    expect($result)->toBe($context);
});

test('allows annual license', function () {
    $configManager = resolve(ConfigManager::class);
    $stage = new GracePeriodStage($configManager->getGracePeriod());

    $license = License::factory()->create([
        'type' => LicenseType::ANNUAL->value,
        'expires_at' => now()->subDay(),
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license,
    );

    $result = $stage($context);

    expect($result)->toBe($context);
});

test('allows credits license', function () {
    $configManager = resolve(ConfigManager::class);
    $stage = new GracePeriodStage($configManager->getGracePeriod());

    $license = License::factory()->create([
        'type' => LicenseType::CREDITS->value,
        'expires_at' => now()->subDay(),
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license,
    );

    $result = $stage($context);

    expect($result)->toBe($context);
});

test('allows expired subscription within grace period', function () {
    $configManager = resolve(ConfigManager::class);
    $stage = new GracePeriodStage($configManager->getGracePeriod());

    $license = License::factory()->create([
        'type' => LicenseType::SUBSCRIPTION->value,
        'expires_at' => now()->subDays(3),
        'grace_ends_at' => now()->addDays(7),
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license,
    );

    $result = $stage($context);

    expect($result)->toBe($context);
});

test('throws exception when expired subscription outside grace period', function () {
    $configManager = resolve(ConfigManager::class);
    $stage = new GracePeriodStage($configManager->getGracePeriod());

    $license = License::factory()->create([
        'type' => LicenseType::SUBSCRIPTION->value,
        'expires_at' => now()->subDays(15),
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license,
    );

    $stage($context);
})->throws(LicenseExpiredException::class);

test('allows active subscription', function () {
    $configManager = resolve(ConfigManager::class);
    $stage = new GracePeriodStage($configManager->getGracePeriod());

    $license = License::factory()->create([
        'type' => LicenseType::SUBSCRIPTION->value,
        'expires_at' => now()->addMonth(),
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license,
    );

    $result = $stage($context);

    expect($result)->toBe($context);
});

test('throws exception when license not loaded', function () {
    $configManager = resolve(ConfigManager::class);
    $stage = new GracePeriodStage($configManager->getGracePeriod());

    $context = new LicenseContext(
        key: LicenseKey::fromString('LLLLLLLL-LLLLLLLL-LLLLLLLL-LLLLLLLL'),
        domain: null,
    );

    $stage($context);
})->throws(LicenseNotLoadedException::class);
