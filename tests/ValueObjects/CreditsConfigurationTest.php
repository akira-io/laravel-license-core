<?php

declare(strict_types=1);

use Akira\LaravelLicense\Support\ConfigManager;
use Akira\LaravelLicense\ValueObjects\CreditsConfiguration;

test('creates credits configuration via config manager', function () {
    $configManager = resolve(ConfigManager::class);
    $config = $configManager->getCredits();

    expect($config)->toBeInstanceOf(CreditsConfiguration::class);
});

test('credits configuration with partial consumption', function () {
    $config = new CreditsConfiguration(
        allowPartialConsumption: true,
        allowRefund: false,
    );

    expect($config->allowPartialConsumption)->toBeTrue()
        ->and($config->allowRefund)->toBeFalse();
});

test('allows refund when enabled', function () {
    $config = new CreditsConfiguration(
        allowPartialConsumption: false,
        allowRefund: true,
    );

    expect($config->allowRefund)->toBeTrue();
});

test('allows both partial consumption and refund', function () {
    $config = new CreditsConfiguration(
        allowPartialConsumption: true,
        allowRefund: true,
    );

    expect($config->allowPartialConsumption)->toBeTrue()
        ->and($config->allowRefund)->toBeTrue();
});

test('disables partial consumption when needed', function () {
    $config = new CreditsConfiguration(
        allowPartialConsumption: false,
        allowRefund: false,
    );

    expect($config->allowPartialConsumption)->toBeFalse();
});

test('disables refund when needed', function () {
    $config = new CreditsConfiguration(
        allowPartialConsumption: false,
        allowRefund: false,
    );

    expect($config->allowRefund)->toBeFalse();
});

test('has both flags enabled', function () {
    $config = new CreditsConfiguration(
        allowPartialConsumption: true,
        allowRefund: true,
    );

    expect($config->allowPartialConsumption)->toBeTrue()
        ->and($config->allowRefund)->toBeTrue();
});
