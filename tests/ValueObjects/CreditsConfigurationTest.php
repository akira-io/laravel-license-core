<?php

declare(strict_types=1);

use Akira\LaravelLicense\ValueObjects\CreditsConfiguration;

test('creates credits configuration from array', function () {
    $config = CreditsConfiguration::fromArray([
        'allow_partial_consumption' => false,
        'allow_refund' => false,
    ]);

    expect($config->allowPartialConsumption)->toBeFalse()
        ->and($config->allowRefund)->toBeFalse();
});

test('uses default values when not provided', function () {
    $config = CreditsConfiguration::fromArray([]);

    expect($config->allowPartialConsumption)->toBeFalse()
        ->and($config->allowRefund)->toBeFalse();
});

test('allows partial consumption when enabled', function () {
    $config = CreditsConfiguration::fromArray([
        'allow_partial_consumption' => true,
    ]);

    expect($config->allowPartialConsumption)->toBeTrue();
});

test('allows refund when enabled', function () {
    $config = CreditsConfiguration::fromArray([
        'allow_refund' => true,
    ]);

    expect($config->allowRefund)->toBeTrue();
});

test('allows both partial consumption and refund', function () {
    $config = CreditsConfiguration::fromArray([
        'allow_partial_consumption' => true,
        'allow_refund' => true,
    ]);

    expect($config->allowPartialConsumption)->toBeTrue()
        ->and($config->allowRefund)->toBeTrue();
});

test('disables partial consumption when needed', function () {
    $config = CreditsConfiguration::fromArray([
        'allow_partial_consumption' => false,
    ]);

    expect($config->allowPartialConsumption)->toBeFalse();
});

test('disables refund when needed', function () {
    $config = CreditsConfiguration::fromArray([
        'allow_refund' => false,
    ]);

    expect($config->allowRefund)->toBeFalse();
});
