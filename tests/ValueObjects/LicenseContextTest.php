<?php

declare(strict_types=1);

use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\ValueObjects\LicenseContext;
use Akira\LaravelLicense\ValueObjects\LicenseKey;

it('creates with all params', function () {
    $k = new LicenseKey('KEY');
    $c = new LicenseContext($k, null, null, null, null);
    expect($c->key)->toBe($k);
});

it('with license', function () {
    $k = new LicenseKey('KEY');
    $l = License::factory()->create();
    $c1 = new LicenseContext($k, null, null, null, null);
    $c2 = $c1->withLicense($l);
    expect($c2->license)->toBe($l)
        ->and($c1)->not->toBe($c2);
});

it('is readonly', function () {
    $context = new LicenseContext(new LicenseKey('K'), null, null, null, null);
    expect(fn () => $context->ip = '1.1.1.1')->toThrow(Error::class);
});
