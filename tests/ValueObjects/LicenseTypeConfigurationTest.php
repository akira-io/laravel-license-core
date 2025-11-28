<?php

declare(strict_types=1);

use Akira\LaravelLicense\ValueObjects\LicenseTypeConfiguration;

test('creates license type configuration from constructor', function () {
    $config = new LicenseTypeConfiguration(
        requiresActivation: true,
        requiresUpdateCheck: true,
        supportsGracePeriod: true,
        fallbackOnExpiry: false,
    );

    expect($config->requiresActivation)->toBeTrue()
        ->and($config->requiresUpdateCheck)->toBeTrue()
        ->and($config->supportsGracePeriod)->toBeTrue()
        ->and($config->fallbackOnExpiry)->toBeFalse();
});

test('uses default values when not provided', function () {
    $config = new LicenseTypeConfiguration(
        requiresActivation: false,
        requiresUpdateCheck: false,
        supportsGracePeriod: false,
        fallbackOnExpiry: false,
    );

    expect($config->requiresActivation)->toBeFalse()
        ->and($config->requiresUpdateCheck)->toBeFalse()
        ->and($config->supportsGracePeriod)->toBeFalse()
        ->and($config->fallbackOnExpiry)->toBeFalse();
});

test('allows no activation requirement', function () {
    $config = new LicenseTypeConfiguration(
        requiresActivation: false,
        requiresUpdateCheck: false,
        supportsGracePeriod: false,
    );

    expect($config->requiresActivation)->toBeFalse();
});

test('allows activation requirement', function () {
    $config = new LicenseTypeConfiguration(
        requiresActivation: true,
        requiresUpdateCheck: false,
        supportsGracePeriod: false,
    );

    expect($config->requiresActivation)->toBeTrue();
});

test('allows no update check requirement', function () {
    $config = new LicenseTypeConfiguration(
        requiresActivation: false,
        requiresUpdateCheck: false,
        supportsGracePeriod: false,
    );

    expect($config->requiresUpdateCheck)->toBeFalse();
});

test('allows update check requirement', function () {
    $config = new LicenseTypeConfiguration(
        requiresActivation: false,
        requiresUpdateCheck: true,
        supportsGracePeriod: false,
    );

    expect($config->requiresUpdateCheck)->toBeTrue();
});

test('allows no grace period support', function () {
    $config = new LicenseTypeConfiguration(
        requiresActivation: false,
        requiresUpdateCheck: false,
        supportsGracePeriod: false,
    );

    expect($config->supportsGracePeriod)->toBeFalse();
});

test('allows grace period support', function () {
    $config = new LicenseTypeConfiguration(
        requiresActivation: false,
        requiresUpdateCheck: false,
        supportsGracePeriod: true,
    );

    expect($config->supportsGracePeriod)->toBeTrue();
});

test('allows fallback on expiry', function () {
    $config = new LicenseTypeConfiguration(
        requiresActivation: false,
        requiresUpdateCheck: false,
        supportsGracePeriod: false,
        fallbackOnExpiry: true,
    );

    expect($config->fallbackOnExpiry)->toBeTrue();
});
