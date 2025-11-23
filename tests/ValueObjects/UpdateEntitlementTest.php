<?php

declare(strict_types=1);

use Akira\LaravelLicense\ValueObjects\UpdateEntitlement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

it('creates with date', function () {
    expect(new UpdateEntitlement(Date::parse('2025-12-31'), false)->updatesUntil)
        ->toBeInstanceOf(Carbon::class);
});

it('null allows all', function () {
    expect(new UpdateEntitlement(null, false)
        ->canInstall(Date::now()))
        ->toBeTrue();
});

it('before allowed', function () {
    expect(new UpdateEntitlement(Date::parse('2025-12-31'), false)
        ->canInstall(Date::parse('2025-01-01')))
        ->toBeTrue();
});

it('after denied', function () {
    expect(new UpdateEntitlement(Date::parse('2025-01-01'), false)
        ->canInstall(Date::parse('2025-12-31')))
        ->toBeFalse();
});

it('equal allowed', function () {
    $d = Date::now();
    expect(new UpdateEntitlement($d, false)
        ->canInstall($d))
        ->toBeTrue();
});
