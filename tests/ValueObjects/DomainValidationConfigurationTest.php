<?php

declare(strict_types=1);

use Akira\LaravelLicense\ValueObjects\DomainValidationConfiguration;

test('creates domain validation configuration from array', function () {
    $config = DomainValidationConfiguration::fromArray([
        'pattern_type' => 'glob',
        'case_sensitive' => false,
    ]);

    expect($config->patternType)->toBe('glob')
        ->and($config->caseSensitive)->toBeFalse();
});

test('uses default values when not provided', function () {
    $config = DomainValidationConfiguration::fromArray([]);

    expect($config->patternType)->toBe('glob')
        ->and($config->caseSensitive)->toBeFalse();
});

test('allows glob pattern type', function () {
    $config = DomainValidationConfiguration::fromArray([
        'pattern_type' => 'glob',
    ]);

    expect($config->patternType)->toBe('glob')
        ->and($config->isValidPatternType())->toBeTrue();
});

test('allows exact pattern type', function () {
    $config = DomainValidationConfiguration::fromArray([
        'pattern_type' => 'exact',
    ]);

    expect($config->patternType)->toBe('exact')
        ->and($config->isValidPatternType())->toBeTrue();
});

test('allows regex pattern type', function () {
    $config = DomainValidationConfiguration::fromArray([
        'pattern_type' => 'regex',
    ]);

    expect($config->patternType)->toBe('regex')
        ->and($config->isValidPatternType())->toBeTrue();
});

test('rejects invalid pattern type', function () {
    $config = DomainValidationConfiguration::fromArray([
        'pattern_type' => 'invalid',
    ]);

    expect($config->isValidPatternType())->toBeFalse();
});

test('allows case sensitive matching', function () {
    $config = DomainValidationConfiguration::fromArray([
        'case_sensitive' => true,
    ]);

    expect($config->caseSensitive)->toBeTrue();
});

test('allows case insensitive matching', function () {
    $config = DomainValidationConfiguration::fromArray([
        'case_sensitive' => false,
    ]);

    expect($config->caseSensitive)->toBeFalse();
});
