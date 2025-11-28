<?php

declare(strict_types=1);

use Akira\LaravelLicense\ValueObjects\GracePeriodConfiguration;

test('creates grace period configuration from array', function () {
    $config = GracePeriodConfiguration::fromArray([
        'lifetime' => null,
        'annual' => null,
        'subscription' => 30,
        'trial' => 7,
        'credits' => null,
    ]);

    expect($config->lifetime)->toBeNull()
        ->and($config->annual)->toBeNull()
        ->and($config->subscription)->toBe(30)
        ->and($config->trial)->toBe(7)
        ->and($config->credits)->toBeNull();
});

test('uses default values when not provided', function () {
    $config = GracePeriodConfiguration::fromArray([]);

    expect($config->lifetime)->toBeNull()
        ->and($config->annual)->toBeNull()
        ->and($config->subscription)->toBe(30)
        ->and($config->trial)->toBe(7)
        ->and($config->credits)->toBeNull();
});

test('allows all types to have grace periods', function () {
    $config = GracePeriodConfiguration::fromArray([
        'lifetime' => 365,
        'annual' => 30,
        'subscription' => 60,
        'trial' => 14,
        'credits' => 90,
    ]);

    expect($config->lifetime)->toBe(365)
        ->and($config->annual)->toBe(30)
        ->and($config->subscription)->toBe(60)
        ->and($config->trial)->toBe(14)
        ->and($config->credits)->toBe(90);
});

test('returns grace days for lifetime type', function () {
    $config = GracePeriodConfiguration::fromArray([
        'lifetime' => 100,
    ]);

    expect($config->getDaysForType('lifetime'))->toBe(100);
});

test('returns grace days for annual type', function () {
    $config = GracePeriodConfiguration::fromArray([
        'annual' => 45,
    ]);

    expect($config->getDaysForType('annual'))->toBe(45);
});

test('returns grace days for subscription type', function () {
    $config = GracePeriodConfiguration::fromArray([
        'subscription' => 30,
    ]);

    expect($config->getDaysForType('subscription'))->toBe(30);
});

test('returns grace days for trial type', function () {
    $config = GracePeriodConfiguration::fromArray([
        'trial' => 7,
    ]);

    expect($config->getDaysForType('trial'))->toBe(7);
});

test('returns grace days for credits type', function () {
    $config = GracePeriodConfiguration::fromArray([
        'credits' => 60,
    ]);

    expect($config->getDaysForType('credits'))->toBe(60);
});

test('returns null for unknown license type', function () {
    $config = GracePeriodConfiguration::fromArray([]);

    expect($config->getDaysForType('unknown'))->toBeNull();
});
