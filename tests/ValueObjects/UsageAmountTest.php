<?php

declare(strict_types=1);
use Akira\LaravelLicense\ValueObjects\UsageAmount;

it('creates with values', function () {
    expect(new UsageAmount(1000, 250))->limit->toBe(1000);
});

it('calculates remaining', function () {
    expect((new UsageAmount(1000, 250))->remaining())->toBe(750);
});

it('zero when equal', function () {
    expect((new UsageAmount(100, 100))->remaining())->toBe(0);
});

it('zero when exceeds', function () {
    expect((new UsageAmount(100, 150))->remaining())->toBe(0);
});

it('has enough true', function () {
    expect((new UsageAmount(1000, 500))->hasEnough(400))->toBeTrue();
});

it('has enough false', function () {
    expect((new UsageAmount(1000, 500))->hasEnough(501))->toBeFalse();
});

it('handles zero', function () {
    expect((new UsageAmount(0, 0))->remaining())->toBe(0);
});

it('is readonly', function () {
    $usage = new UsageAmount(100, 50);
    expect(fn () => $usage->limit = 200)->toThrow(Error::class);
});
