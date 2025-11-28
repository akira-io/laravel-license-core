<?php

declare(strict_types=1);

use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Exceptions\InsufficientCreditsException;
use Akira\LaravelLicense\Exceptions\LicenseNotLoadedException;
use Akira\LaravelLicense\Exceptions\UsageNotConfiguredException;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseUsage;
use Akira\LaravelLicense\Pipelines\Stages\CreditsUsageStage;
use Akira\LaravelLicense\ValueObjects\LicenseContext;
use Akira\LaravelLicense\ValueObjects\LicenseKey;

test('passes context when license type is not credits', function () {
    $stage = new CreditsUsageStage(amountToConsume: 10);

    $license = License::factory()->create(['type' => LicenseType::LIFETIME->value]);
    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license,
    );

    $result = $stage($context);

    expect($result)->toBe($context);
});

test('throws exception when license is not loaded', function () {
    $stage = new CreditsUsageStage(amountToConsume: 10);

    $context = new LicenseContext(
        key: LicenseKey::fromString('TEST-1234-5678-90AB'),
        domain: null,
    );

    $stage($context);
})->throws(LicenseNotLoadedException::class);

test('throws exception when credits license has no usage configured', function () {
    $stage = new CreditsUsageStage(amountToConsume: 10);

    $license = License::factory()->create(['type' => LicenseType::CREDITS->value]);
    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license,
    );

    $stage($context);
})->throws(UsageNotConfiguredException::class);

test('throws exception when insufficient credits available', function () {
    $stage = new CreditsUsageStage(amountToConsume: 10);

    $license = License::factory()->create(['type' => LicenseType::CREDITS->value]);
    LicenseUsage::factory()->create([
        'license_id' => $license->id,
        'limit' => 100,
        'consumed_units' => 95,
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license->fresh(),
    );

    $stage($context);
})->throws(InsufficientCreditsException::class);

test('passes when sufficient credits available', function () {
    $stage = new CreditsUsageStage(amountToConsume: 10);

    $license = License::factory()->create(['type' => LicenseType::CREDITS->value]);
    LicenseUsage::factory()->create([
        'license_id' => $license->id,
        'limit' => 100,
        'consumed_units' => 50,
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license->fresh(),
    );

    $result = $stage($context);

    expect($result)->toBe($context);
});

test('passes when amount to consume is zero', function () {
    $stage = new CreditsUsageStage(amountToConsume: 0);

    $license = License::factory()->create(['type' => LicenseType::CREDITS->value]);
    LicenseUsage::factory()->create([
        'license_id' => $license->id,
        'limit' => 100,
        'consumed_units' => 100,
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license->fresh(),
    );

    $result = $stage($context);

    expect($result)->toBe($context);
});

test('passes when exactly enough credits available', function () {
    $stage = new CreditsUsageStage(amountToConsume: 10);

    $license = License::factory()->create(['type' => LicenseType::CREDITS->value]);
    LicenseUsage::factory()->create([
        'license_id' => $license->id,
        'limit' => 100,
        'consumed_units' => 90,
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license->fresh(),
    );

    $result = $stage($context);

    expect($result)->toBe($context);
});
