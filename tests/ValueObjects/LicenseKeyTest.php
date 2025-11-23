<?php

declare(strict_types=1);

use Akira\LaravelLicense\ValueObjects\LicenseKey;

it('can create license key with value', function () {
    $key = new LicenseKey('TEST-KEY-123');

    expect($key->value)->toBe('TEST-KEY-123');
});

it('can convert to string', function () {
    $key = new LicenseKey('TEST-KEY-456');

    expect((string) $key)->toBe('TEST-KEY-456');
});

it('can create from string', function () {
    $key = LicenseKey::fromString('TEST-KEY-789');

    expect($key)->toBeInstanceOf(LicenseKey::class)
        ->and($key->value)->toBe('TEST-KEY-789');
});

it('is readonly', function () {
    $key = new LicenseKey('TEST-KEY');

    expect(fn () => $key->value = 'NEW-KEY')
        ->toThrow(Error::class);
});

it('accepts uuid format', function () {
    $uuid = '550e8400-e29b-41d4-a716-446655440000';
    $key = new LicenseKey($uuid);

    expect($key->value)->toBe($uuid);
});

it('accepts any string format', function () {
    $formats = [
        'XXXX-XXXX-XXXX-XXXX',
        'simple-key',
        'key_with_underscore',
        'KEY123',
        '1234567890',
    ];

    foreach ($formats as $format) {
        $key = LicenseKey::fromString($format);
        expect($key->value)->toBe($format);
    }
});

it('handles empty string', function () {
    $key = new LicenseKey('');

    expect($key->value)->toBe('');
});
