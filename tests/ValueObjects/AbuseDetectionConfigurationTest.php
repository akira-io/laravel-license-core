<?php

declare(strict_types=1);

use Akira\LaravelLicense\ValueObjects\AbuseDetectionConfiguration;

test('creates abuse detection configuration from constructor', function () {
    $config = new AbuseDetectionConfiguration(
        enabled: true,
        windowMinutes: 10,
        activationThreshold: 10,
        eventsToMonitor: ['activated'],
        actionOnAbuse: 'log',
    );

    expect($config->enabled)->toBeTrue()
        ->and($config->windowMinutes)->toBe(10)
        ->and($config->activationThreshold)->toBe(10)
        ->and($config->eventsToMonitor)->toBe(['activated'])
        ->and($config->actionOnAbuse)->toBe('log');
});

test('uses default values when not provided', function () {
    $config = new AbuseDetectionConfiguration(
        enabled: true,
        windowMinutes: 10,
        activationThreshold: 10,
        eventsToMonitor: ['activated'],
        actionOnAbuse: 'log',
    );

    expect($config->enabled)->toBeTrue()
        ->and($config->windowMinutes)->toBe(10)
        ->and($config->activationThreshold)->toBe(10)
        ->and($config->eventsToMonitor)->toBe(['activated'])
        ->and($config->actionOnAbuse)->toBe('log');
});

test('can disable abuse detection', function () {
    $config = new AbuseDetectionConfiguration(
        enabled: false,
        windowMinutes: 10,
        activationThreshold: 10,
        eventsToMonitor: ['activated'],
        actionOnAbuse: 'log',
    );

    expect($config->enabled)->toBeFalse();
});

test('allows custom window minutes', function () {
    $config = new AbuseDetectionConfiguration(
        enabled: true,
        windowMinutes: 30,
        activationThreshold: 10,
        eventsToMonitor: ['activated'],
        actionOnAbuse: 'log',
    );

    expect($config->windowMinutes)->toBe(30);
});

test('allows custom activation threshold', function () {
    $config = new AbuseDetectionConfiguration(
        enabled: true,
        windowMinutes: 10,
        activationThreshold: 20,
        eventsToMonitor: ['activated'],
        actionOnAbuse: 'log',
    );

    expect($config->activationThreshold)->toBe(20);
});

test('allows custom events to monitor', function () {
    $config = new AbuseDetectionConfiguration(
        enabled: true,
        windowMinutes: 10,
        activationThreshold: 10,
        eventsToMonitor: ['activated', 'domain_changed'],
        actionOnAbuse: 'log',
    );

    expect($config->eventsToMonitor)->toBe(['activated', 'domain_changed']);
});

test('allows custom action on abuse', function () {
    $config = new AbuseDetectionConfiguration(
        enabled: true,
        windowMinutes: 10,
        activationThreshold: 10,
        eventsToMonitor: ['activated'],
        actionOnAbuse: 'suspend',
    );

    expect($config->actionOnAbuse)->toBe('suspend');
});
