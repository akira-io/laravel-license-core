<?php

declare(strict_types=1);
use Akira\LaravelLicense\ValueObjects\LicenseMeta;

it('creates with data', function () {
    expect((new LicenseMeta(['key' => 'val']))->data)->toBe(['key' => 'val']);
});

it('creates with null', function () {
    expect((new LicenseMeta(null))->data)->toBeNull();
});

it('gets value', function () {
    expect((new LicenseMeta(['k' => 'v']))->get('k'))->toBe('v');
});

it('gets default', function () {
    expect((new LicenseMeta(['k' => 'v']))->get('x', 'def'))->toBe('def');
});

it('null default', function () {
    expect((new LicenseMeta(['k' => 'v']))->get('x'))->toBeNull();
});

it('null data', function () {
    expect((new LicenseMeta(null))->get('k'))->toBeNull();
});

it('nested array', function () {
    expect((new LicenseMeta(['a' => ['b' => 1]]))->get('a'))->toBe(['b' => 1]);
});

it('is readonly', function () {
    $meta = new LicenseMeta([]);
    expect(fn () => $meta->data = ['new'])->toThrow(Error::class);
});
