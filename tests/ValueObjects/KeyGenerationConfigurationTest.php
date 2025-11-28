<?php

declare(strict_types=1);

use Akira\LaravelLicense\Support\ConfigManager;
use Akira\LaravelLicense\ValueObjects\KeyGenerationConfiguration;

test('creates key generation configuration via config manager', function () {
    $configManager = resolve(ConfigManager::class);
    $config = $configManager->getKeyGeneration();

    expect($config)->toBeInstanceOf(KeyGenerationConfiguration::class);
});

test('creates key generation with custom values', function () {
    $config = new KeyGenerationConfiguration(
        prefix: 'LIC',
        format: 'uuid',
    );

    expect($config->prefix)->toBe('LIC')
        ->and($config->format)->toBe('uuid');
});

test('allows custom prefix', function () {
    $config = new KeyGenerationConfiguration(
        prefix: 'KEY',
        format: 'uuid',
    );

    expect($config->prefix)->toBe('KEY');
});

test('allows uuid format', function () {
    $config = new KeyGenerationConfiguration(
        prefix: 'LIC',
        format: 'uuid',
    );

    expect($config->format)->toBe('uuid')
        ->and($config->isValidFormat())->toBeTrue();
});

test('allows sequential format', function () {
    $config = new KeyGenerationConfiguration(
        prefix: 'LIC',
        format: 'sequential',
    );

    expect($config->format)->toBe('sequential')
        ->and($config->isValidFormat())->toBeTrue();
});

test('rejects invalid format', function () {
    $config = new KeyGenerationConfiguration(
        prefix: 'LIC',
        format: 'invalid',
    );

    expect($config->isValidFormat())->toBeFalse();
});

test('allows empty prefix', function () {
    $config = new KeyGenerationConfiguration(
        prefix: '',
        format: 'uuid',
    );

    expect($config->prefix)->toBe('');
});

test('allows long prefix', function () {
    $config = new KeyGenerationConfiguration(
        prefix: 'PROFESSIONAL-LICENSE',
        format: 'uuid',
    );

    expect($config->prefix)->toBe('PROFESSIONAL-LICENSE');
});
