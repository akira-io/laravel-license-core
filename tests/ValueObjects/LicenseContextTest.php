<?php

declare(strict_types=1);

use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\ValueObjects\DomainName;
use Akira\LaravelLicense\ValueObjects\LicenseContext;
use Akira\LaravelLicense\ValueObjects\LicenseKey;
use Akira\LaravelLicense\ValueObjects\MachineFingerprint;
use Illuminate\Support\Facades\Date;

it('creates with all params', function () {
    $key = new LicenseKey('KEY');
    $domain = new DomainName('example.com');
    $machine = new MachineFingerprint('fingerprint123');
    $license = License::factory()->create();
    $releaseDate = Date::parse('2024-01-01');

    $context = new LicenseContext($key, $domain, $machine, $license, $releaseDate);

    expect($context->key)->toBe($key)
        ->and($context->domain)->toBe($domain)
        ->and($context->machineFingerprint)->toBe($machine)
        ->and($context->license)->toBe($license)
        ->and($context->releaseDate)->toEqual($releaseDate);
});

it('creates with minimal params', function () {
    $key = new LicenseKey('KEY');
    $context = new LicenseContext($key, null);

    expect($context->key)->toBe($key)
        ->and($context->domain)->toBeNull()
        ->and($context->machineFingerprint)->toBeNull()
        ->and($context->license)->toBeNull()
        ->and($context->releaseDate)->toBeNull();
});

it('with license creates new instance', function () {
    $key = new LicenseKey('KEY');
    $domain = new DomainName('example.com');
    $machine = new MachineFingerprint('fingerprint123');
    $license = License::factory()->create();

    $context1 = new LicenseContext($key, $domain, $machine, null, Date::parse('2024-01-01'));
    $context2 = $context1->withLicense($license);

    expect($context2->license)->toBe($license)
        ->and($context2->key)->toBe($key)
        ->and($context2->domain)->toBe($domain)
        ->and($context2->machineFingerprint)->toBe($machine)
        ->and($context2->releaseDate)->toEqual(Date::parse('2024-01-01'))
        ->and($context1)->not->toBe($context2)
        ->and($context1->license)->toBeNull();
});

it('with release date creates new instance', function () {
    $key = new LicenseKey('KEY');
    $domain = new DomainName('example.com');
    $machine = new MachineFingerprint('fingerprint123');
    $license = License::factory()->create();
    $releaseDate = Date::parse('2024-12-31');

    $context1 = new LicenseContext($key, $domain, $machine, $license, null);
    $context2 = $context1->withReleaseDate($releaseDate);

    expect($context2->releaseDate)->toEqual($releaseDate)
        ->and($context2->key)->toBe($key)
        ->and($context2->domain)->toBe($domain)
        ->and($context2->machineFingerprint)->toBe($machine)
        ->and($context2->license)->toBe($license)
        ->and($context1)->not->toBe($context2)
        ->and($context1->releaseDate)->toBeNull();
});

it('is readonly', function () {
    $context = new LicenseContext(new LicenseKey('K'), null);
    expect(fn () => $context->key = new LicenseKey('NEW'))->toThrow(Error::class);
});
