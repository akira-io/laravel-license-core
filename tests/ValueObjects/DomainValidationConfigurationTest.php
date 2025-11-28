<?php

declare(strict_types=1);

use Akira\LaravelLicense\ValueObjects\DomainValidationConfiguration;

test('creates domain validation configuration from constructor', function () {
    $config = new DomainValidationConfiguration(
        patternType: 'glob',
        caseSensitive: false,
    );

    expect($config->patternType)->toBe('glob')
        ->and($config->caseSensitive)->toBeFalse();
});

test('uses default values when not provided', function () {
    $config = new DomainValidationConfiguration(
        patternType: 'glob',
        caseSensitive: false,
    );

    expect($config->patternType)->toBe('glob')
        ->and($config->caseSensitive)->toBeFalse();
});

test('allows glob pattern type', function () {
    $config = new DomainValidationConfiguration(
        patternType: 'glob',
        caseSensitive: false,
    );

    expect($config->patternType)->toBe('glob')
        ->and($config->isValidPatternType())->toBeTrue();
});

test('allows exact pattern type', function () {
    $config = new DomainValidationConfiguration(
        patternType: 'exact',
        caseSensitive: false,
    );

    expect($config->patternType)->toBe('exact')
        ->and($config->isValidPatternType())->toBeTrue();
});

test('allows regex pattern type', function () {
    $config = new DomainValidationConfiguration(
        patternType: 'regex',
        caseSensitive: false,
    );

    expect($config->patternType)->toBe('regex')
        ->and($config->isValidPatternType())->toBeTrue();
});

test('rejects invalid pattern type', function () {
    $config = new DomainValidationConfiguration(
        patternType: 'invalid',
        caseSensitive: false,
    );

    expect($config->isValidPatternType())->toBeFalse();
});

test('allows case sensitive matching', function () {
    $config = new DomainValidationConfiguration(
        patternType: 'glob',
        caseSensitive: true,
    );

    expect($config->caseSensitive)->toBeTrue();
});

test('allows case insensitive matching', function () {
    $config = new DomainValidationConfiguration(
        patternType: 'glob',
        caseSensitive: false,
    );

    expect($config->caseSensitive)->toBeFalse();
});
