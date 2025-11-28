<?php

declare(strict_types=1);

use Akira\LaravelLicense\Exceptions\LicenseNotFoundException;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Pipelines\Stages\ResolveLicenseStage;
use Akira\LaravelLicense\ValueObjects\LicenseContext;
use Akira\LaravelLicense\ValueObjects\LicenseKey;

it('resolves license successfully', function () {
    $license = License::factory()->create(['key' => 'VALID-KEY-1234']);

    $context = new LicenseContext(
        key: LicenseKey::fromString('VALID-KEY-1234'),
        domain: null
    );

    $stage = new ResolveLicenseStage();
    $result = $stage($context);

    expect($result->license)->toBeInstanceOf(License::class)
        ->and($result->license->key)->toBe($license->key);
});

it('throws exception when license not found', function () {
    $context = new LicenseContext(
        key: LicenseKey::fromString('INVALID-KEY'),
        domain: null
    );

    $stage = new ResolveLicenseStage();

    expect(fn () => $stage($context))
        ->toThrow(LicenseNotFoundException::class);
});

it('returns context with license attached', function () {
    $license = License::factory()->create(['key' => 'TEST-KEY-5678']);

    $context = new LicenseContext(
        key: LicenseKey::fromString('TEST-KEY-5678'),
        domain: null
    );

    $stage = new ResolveLicenseStage();
    $result = $stage($context);

    expect($result)
        ->toBeInstanceOf(LicenseContext::class)
        ->and($result->license)->not->toBeNull()
        ->and($result->license->key)->toBe('TEST-KEY-5678');
});
