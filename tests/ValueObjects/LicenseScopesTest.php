<?php

declare(strict_types=1);
use Akira\LaravelLicense\ValueObjects\LicenseScopes;

it('creates with array', function () {
    expect((new LicenseScopes(['read']))->scopes)->toBe(['read']);
});

it('creates with null', function () {
    expect((new LicenseScopes(null))->scopes)->toBeNull();
});

it('has scope true', function () {
    expect((new LicenseScopes(['read', 'write']))->has('read'))->toBeTrue();
});

it('has scope false', function () {
    expect((new LicenseScopes(['read']))->has('write'))->toBeFalse();
});

it('null scopes false', function () {
    expect((new LicenseScopes(null))->has('read'))->toBeFalse();
});

it('empty array false', function () {
    expect((new LicenseScopes([]))->has('read'))->toBeFalse();
});

it('is readonly', function () {
    $scopes = new LicenseScopes([]);
    expect(fn () => $scopes->scopes = ['new'])->toThrow(Error::class);
});
