<?php

declare(strict_types=1);

use Akira\LaravelLicense\ValueObjects\LicenseTypeConfiguration;

test('creates license type configuration from array', function () {
    $config = LicenseTypeConfiguration::fromArray([
        'requires_activation' => true,
        'requires_update_check' => true,
        'supports_grace_period' => true,
        'fallback_on_expiry' => false,
    ]);

    expect($config->requiresActivation)->toBeTrue()
        ->and($config->requiresUpdateCheck)->toBeTrue()
        ->and($config->supportsGracePeriod)->toBeTrue()
        ->and($config->fallbackOnExpiry)->toBeFalse();
});

test('uses default values when not provided', function () {
    $config = LicenseTypeConfiguration::fromArray([]);

    expect($config->requiresActivation)->toBeFalse()
        ->and($config->requiresUpdateCheck)->toBeFalse()
        ->and($config->supportsGracePeriod)->toBeFalse()
        ->and($config->fallbackOnExpiry)->toBeFalse();
});

test('allows no activation requirement', function () {
    $config = LicenseTypeConfiguration::fromArray([
        'requires_activation' => false,
    ]);

    expect($config->requiresActivation)->toBeFalse();
});

test('allows activation requirement', function () {
    $config = LicenseTypeConfiguration::fromArray([
        'requires_activation' => true,
    ]);

    expect($config->requiresActivation)->toBeTrue();
});

test('allows no update check requirement', function () {
    $config = LicenseTypeConfiguration::fromArray([
        'requires_update_check' => false,
    ]);

    expect($config->requiresUpdateCheck)->toBeFalse();
});

test('allows update check requirement', function () {
    $config = LicenseTypeConfiguration::fromArray([
        'requires_update_check' => true,
    ]);

    expect($config->requiresUpdateCheck)->toBeTrue();
});

test('allows no grace period support', function () {
    $config = LicenseTypeConfiguration::fromArray([
        'supports_grace_period' => false,
    ]);

    expect($config->supportsGracePeriod)->toBeFalse();
});

test('allows grace period support', function () {
    $config = LicenseTypeConfiguration::fromArray([
        'supports_grace_period' => true,
    ]);

    expect($config->supportsGracePeriod)->toBeTrue();
});

test('allows fallback on expiry', function () {
    $config = LicenseTypeConfiguration::fromArray([
        'fallback_on_expiry' => true,
    ]);

    expect($config->fallbackOnExpiry)->toBeTrue();
});
