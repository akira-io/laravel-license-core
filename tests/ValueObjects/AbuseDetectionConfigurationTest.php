<?php

declare(strict_types=1);

use Akira\LaravelLicense\ValueObjects\AbuseDetectionConfiguration;

test('creates abuse detection configuration from array', function () {
    $config = AbuseDetectionConfiguration::fromArray([
        'enabled' => true,
        'window_minutes' => 10,
        'activation_threshold' => 10,
        'events_to_monitor' => ['activated'],
        'action_on_abuse' => 'log',
    ]);

    expect($config->enabled)->toBeTrue()
        ->and($config->windowMinutes)->toBe(10)
        ->and($config->activationThreshold)->toBe(10)
        ->and($config->eventsToMonitor)->toBe(['activated'])
        ->and($config->actionOnAbuse)->toBe('log');
});

test('uses default values when not provided', function () {
    $config = AbuseDetectionConfiguration::fromArray([]);

    expect($config->enabled)->toBeTrue()
        ->and($config->windowMinutes)->toBe(10)
        ->and($config->activationThreshold)->toBe(10)
        ->and($config->eventsToMonitor)->toBe(['activated'])
        ->and($config->actionOnAbuse)->toBe('log');
});

test('can disable abuse detection', function () {
    $config = AbuseDetectionConfiguration::fromArray([
        'enabled' => false,
    ]);

    expect($config->enabled)->toBeFalse();
});

test('allows custom window minutes', function () {
    $config = AbuseDetectionConfiguration::fromArray([
        'window_minutes' => 30,
    ]);

    expect($config->windowMinutes)->toBe(30);
});

test('allows custom activation threshold', function () {
    $config = AbuseDetectionConfiguration::fromArray([
        'activation_threshold' => 20,
    ]);

    expect($config->activationThreshold)->toBe(20);
});

test('allows custom events to monitor', function () {
    $config = AbuseDetectionConfiguration::fromArray([
        'events_to_monitor' => ['activated', 'domain_changed'],
    ]);

    expect($config->eventsToMonitor)->toBe(['activated', 'domain_changed']);
});

test('allows custom action on abuse', function () {
    $config = AbuseDetectionConfiguration::fromArray([
        'action_on_abuse' => 'suspend',
    ]);

    expect($config->actionOnAbuse)->toBe('suspend');
});
