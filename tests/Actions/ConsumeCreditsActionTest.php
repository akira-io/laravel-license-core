<?php

declare(strict_types=1);

use Akira\LaravelLicense\Actions\ConsumeCreditsAction;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Exceptions\UsageNotConfiguredException;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseUsage;

it('can consume credits when available', function () {
    $license = License::factory()
        ->has(LicenseUsage::factory()->state(['limit' => 1000,
            'consumed_units' => 100,
        ]), 'usages')
        ->create();

    $action = new ConsumeCreditsAction();
    $result = $action->handle($license, 50);

    expect($result)->toBeTrue();

    $usage = $license->usages()->first();
    expect($usage->consumed_units)->toBe(150);
});

it('returns false when insufficient credits', function () {
    $license = License::factory()
        ->has(LicenseUsage::factory()->state(['limit' => 1000,
            'consumed_units' => 990,
        ]), 'usages')
        ->create();

    $action = new ConsumeCreditsAction();
    $result = $action->handle($license, 50);

    expect($result)->toBeFalse();

    $usage = $license->usages()->first();
    expect($usage->consumed_units)->toBe(990);
});

it('throws exception when usage not configured', function () {
    $license = License::factory()->create();

    $action = new ConsumeCreditsAction();

    expect(fn () => $action->handle($license, 10))
        ->toThrow(UsageNotConfiguredException::class);
});

it('can consume exact remaining amount', function () {
    $license = License::factory()
        ->has(LicenseUsage::factory()->state(['limit' => 1000,
            'consumed_units' => 900,
        ]), 'usages')
        ->create();

    $action = new ConsumeCreditsAction();
    $result = $action->handle($license, 100);

    expect($result)->toBeTrue();

    $usage = $license->usages()->first();
    expect($usage->consumed_units)->toBe(1000)
        ->and($usage->remaining())->toBe(0);
});

it('returns false when trying to consume more than remaining', function () {
    $license = License::factory()
        ->has(LicenseUsage::factory()->state(['limit' => 1000,
            'consumed_units' => 900,
        ]), 'usages')
        ->create();

    $action = new ConsumeCreditsAction();
    $result = $action->handle($license, 101);

    expect($result)->toBeFalse();

    $usage = $license->usages()->first();
    expect($usage->consumed_units)->toBe(900);
});

it('can consume zero credits', function () {
    $license = License::factory()
        ->has(LicenseUsage::factory()->state(['limit' => 1000,
            'consumed_units' => 100,
        ]), 'usages')
        ->create();

    $action = new ConsumeCreditsAction();
    $result = $action->handle($license, 0);

    expect($result)->toBeTrue();

    $usage = $license->usages()->first();
    expect($usage->consumed_units)->toBe(100);
});

it('handles multiple consecutive consumptions', function () {
    $license = License::factory()
        ->has(LicenseUsage::factory()->state(['limit' => 1000,
            'consumed_units' => 0,
        ]), 'usages')
        ->create();

    $action = new ConsumeCreditsAction();

    expect($action->handle($license, 100))->toBeTrue();
    expect($action->handle($license, 200))->toBeTrue();
    expect($action->handle($license, 300))->toBeTrue();

    $usage = $license->usages()->first();
    expect($usage->consumed_units)->toBe(600)
        ->and($usage->remaining())->toBe(400);
});

it('stops consuming when limit reached', function () {
    $license = License::factory()
        ->has(LicenseUsage::factory()->state(['limit' => 100,
            'consumed_units' => 0,
        ]), 'usages')
        ->create();

    $action = new ConsumeCreditsAction();

    expect($action->handle($license, 50))->toBeTrue();
    expect($action->handle($license, 50))->toBeTrue();
    expect($action->handle($license, 1))->toBeFalse();

    $usage = $license->usages()->first();
    expect($usage->consumed_units)->toBe(100);
});

it('is readonly class', function () {
    $reflection = new ReflectionClass(ConsumeCreditsAction::class);

    expect($reflection->isReadOnly())->toBeTrue();
});

it('is final class', function () {
    $reflection = new ReflectionClass(ConsumeCreditsAction::class);

    expect($reflection->isFinal())->toBeTrue();
});

it('updates database when consuming credits', function () {
    $license = License::factory()
        ->has(LicenseUsage::factory()->state(['limit' => 1000,
            'consumed_units' => 100,
        ]), 'usages')
        ->create();

    $usage = $license->usages()->first();

    $action = new ConsumeCreditsAction();
    $action->handle($license, 50);

    $this->assertDatabaseHas('license_usages', [
        'id' => $usage->id,
        'consumed_units' => 150,
    ]);
});

it('works with credits license type', function () {
    $license = License::factory()
        ->state(['type' => LicenseType::CREDITS])
        ->has(LicenseUsage::factory()->state(['limit' => 1000,
            'consumed_units' => 0,
        ]), 'usages')
        ->create();

    $action = new ConsumeCreditsAction();
    $result = $action->handle($license, 100);

    expect($result)->toBeTrue()
        ->and($license->usages()->first()->consumed_units)->toBe(100);
});

it('handles large amounts', function () {
    $license = License::factory()
        ->has(LicenseUsage::factory()->state(['limit' => 1000000,
            'consumed_units' => 0,
        ]), 'usages')
        ->create();

    $action = new ConsumeCreditsAction();
    $result = $action->handle($license, 999999);

    expect($result)->toBeTrue();

    $usage = $license->usages()->first();
    expect($usage->consumed_units)->toBe(999999)
        ->and($usage->remaining())->toBe(1);
});
