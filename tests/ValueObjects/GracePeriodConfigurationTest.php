<?php

declare(strict_types=1);

use Akira\LaravelLicense\ValueObjects\GracePeriodConfiguration;

test('creates grace period configuration from constructor', function () {
    $config = new GracePeriodConfiguration(
        lifetime: null,
        annual: null,
        subscription: 30,
        trial: 7,
        credits: null,
    );

    expect($config->lifetime)->toBeNull()
        ->and($config->annual)->toBeNull()
        ->and($config->subscription)->toBe(30)
        ->and($config->trial)->toBe(7)
        ->and($config->credits)->toBeNull();
});

test('uses default values when not provided', function () {
    $config = new GracePeriodConfiguration(
        lifetime: null,
        annual: null,
        subscription: 30,
        trial: 7,
        credits: null,
    );

    expect($config->lifetime)->toBeNull()
        ->and($config->annual)->toBeNull()
        ->and($config->subscription)->toBe(30)
        ->and($config->trial)->toBe(7)
        ->and($config->credits)->toBeNull();
});

test('allows all types to have grace periods', function () {
    $config = new GracePeriodConfiguration(
        lifetime: 365,
        annual: 30,
        subscription: 60,
        trial: 14,
        credits: 90,
    );

    expect($config->lifetime)->toBe(365)
        ->and($config->annual)->toBe(30)
        ->and($config->subscription)->toBe(60)
        ->and($config->trial)->toBe(14)
        ->and($config->credits)->toBe(90);
});

test('returns grace days for lifetime type', function () {
    $config = new GracePeriodConfiguration(
        lifetime: 100,
        annual: null,
        subscription: 30,
        trial: 7,
        credits: null,
    );

    expect($config->getDaysForType('lifetime'))->toBe(100);
});

test('returns grace days for annual type', function () {
    $config = new GracePeriodConfiguration(
        lifetime: null,
        annual: 45,
        subscription: 30,
        trial: 7,
        credits: null,
    );

    expect($config->getDaysForType('annual'))->toBe(45);
});

test('returns grace days for subscription type', function () {
    $config = new GracePeriodConfiguration(
        lifetime: null,
        annual: null,
        subscription: 30,
        trial: 7,
        credits: null,
    );

    expect($config->getDaysForType('subscription'))->toBe(30);
});

test('returns grace days for trial type', function () {
    $config = new GracePeriodConfiguration(
        lifetime: null,
        annual: null,
        subscription: 30,
        trial: 7,
        credits: null,
    );

    expect($config->getDaysForType('trial'))->toBe(7);
});

test('returns grace days for credits type', function () {
    $config = new GracePeriodConfiguration(
        lifetime: null,
        annual: null,
        subscription: 30,
        trial: 7,
        credits: 60,
    );

    expect($config->getDaysForType('credits'))->toBe(60);
});

test('returns null for unknown license type', function () {
    $config = new GracePeriodConfiguration(
        lifetime: null,
        annual: null,
        subscription: 30,
        trial: 7,
        credits: null,
    );

    expect($config->getDaysForType('unknown'))->toBeNull();
});
