<?php

declare(strict_types=1);

use Akira\LaravelLicense\ValueObjects\KeyGenerationConfiguration;

test('creates key generation configuration from array', function () {
    $config = KeyGenerationConfiguration::fromArray([
        'prefix' => 'LIC',
        'format' => 'uuid',
    ]);

    expect($config->prefix)->toBe('LIC')
        ->and($config->format)->toBe('uuid');
});

test('uses default values when not provided', function () {
    $config = KeyGenerationConfiguration::fromArray([]);

    expect($config->prefix)->toBe('LIC')
        ->and($config->format)->toBe('uuid');
});

test('allows custom prefix', function () {
    $config = KeyGenerationConfiguration::fromArray([
        'prefix' => 'KEY',
    ]);

    expect($config->prefix)->toBe('KEY');
});

test('allows uuid format', function () {
    $config = KeyGenerationConfiguration::fromArray([
        'format' => 'uuid',
    ]);

    expect($config->format)->toBe('uuid')
        ->and($config->isValidFormat())->toBeTrue();
});

test('allows sequential format', function () {
    $config = KeyGenerationConfiguration::fromArray([
        'format' => 'sequential',
    ]);

    expect($config->format)->toBe('sequential')
        ->and($config->isValidFormat())->toBeTrue();
});

test('rejects invalid format', function () {
    $config = KeyGenerationConfiguration::fromArray([
        'format' => 'invalid',
    ]);

    expect($config->isValidFormat())->toBeFalse();
});

test('allows empty prefix', function () {
    $config = KeyGenerationConfiguration::fromArray([
        'prefix' => '',
    ]);

    expect($config->prefix)->toBe('');
});

test('allows long prefix', function () {
    $config = KeyGenerationConfiguration::fromArray([
        'prefix' => 'PROFESSIONAL-LICENSE',
    ]);

    expect($config->prefix)->toBe('PROFESSIONAL-LICENSE');
});
