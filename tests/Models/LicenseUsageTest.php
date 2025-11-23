<?php

declare(strict_types=1);

use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseUsage;
use Akira\LaravelLicense\Support\ConfigManager;

beforeEach(function () {
    $this->license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => 'standard',
        'status' => 'active',
        'max_activations' => 5,
        'max_seats' => 10,
    ]);
});

it('can create license usage', function () {
    $usage = LicenseUsage::query()->create([
        'license_id' => $this->license->id,
        'consumed_units' => 50,
        'limit' => 100,
    ]);

    expect($usage)->toBeInstanceOf(LicenseUsage::class)
        ->and($usage->license_id)->toBe($this->license->id)
        ->and($usage->consumed_units)->toBe(50)
        ->and($usage->limit)->toBe(100);
});

it('belongs to a license', function () {
    $usage = LicenseUsage::query()->create([
        'license_id' => $this->license->id,
        'consumed_units' => 25,
        'limit' => 100,
    ]);

    expect($usage->license)->toBeInstanceOf(License::class)
        ->and($usage->license->id)->toBe($this->license->id);
});

it('calculates remaining units correctly', function () {
    $usage = LicenseUsage::query()->create([
        'license_id' => $this->license->id,
        'consumed_units' => 30,
        'limit' => 100,
    ]);

    expect($usage->remaining())->toBe(70);
});

it('returns zero remaining when consumed equals limit', function () {
    $usage = LicenseUsage::query()->create([
        'license_id' => $this->license->id,
        'consumed_units' => 100,
        'limit' => 100,
    ]);

    expect($usage->remaining())->toBe(0);
});

it('returns zero remaining when consumed exceeds limit', function () {
    $usage = LicenseUsage::query()->create([
        'license_id' => $this->license->id,
        'consumed_units' => 150,
        'limit' => 100,
    ]);

    expect($usage->remaining())->toBe(0);
});

it('handles zero consumed units', function () {
    $usage = LicenseUsage::query()->create([
        'license_id' => $this->license->id,
        'consumed_units' => 0,
        'limit' => 100,
    ]);

    expect($usage->remaining())->toBe(100);
});

it('handles zero limit', function () {
    $usage = LicenseUsage::query()->create([
        'license_id' => $this->license->id,
        'consumed_units' => 0,
        'limit' => 0,
    ]);

    expect($usage->remaining())->toBe(0);
});

it('uses custom table name from config', function () {
    config()->set('license.tables.usages', 'custom_usages');

    $usage = new LicenseUsage();

    expect($usage->getTable())->toBe('custom_usages');
});

it('uses default table name', function () {
    $configManager = app(ConfigManager::class);
    $usage = new LicenseUsage();

    expect($usage->getTable())->toBe($configManager->getUsagesTable());
});

it('has fillable attributes', function () {
    $usage = new LicenseUsage();

    expect($usage->getFillable())->toBe([
        'license_id',
        'consumed_units',
        'limit',
    ]);
});

it('can update consumed units', function () {
    $usage = LicenseUsage::query()->create([
        'license_id' => $this->license->id,
        'consumed_units' => 10,
        'limit' => 100,
    ]);

    $usage->update(['consumed_units' => 50]);

    expect($usage->fresh()->consumed_units)->toBe(50)
        ->and($usage->fresh()->remaining())->toBe(50);
});

it('can update limit', function () {
    $usage = LicenseUsage::query()->create([
        'license_id' => $this->license->id,
        'consumed_units' => 50,
        'limit' => 100,
    ]);

    $usage->update(['limit' => 200]);

    expect($usage->fresh()->limit)->toBe(200)
        ->and($usage->fresh()->remaining())->toBe(150);
});

it('remaining returns integer type', function () {
    $usage = LicenseUsage::query()->create([
        'license_id' => $this->license->id,
        'consumed_units' => 30,
        'limit' => 100,
    ]);

    expect($usage->remaining())->toBeInt();
});

it('handles large numbers', function () {
    $usage = LicenseUsage::query()->create([
        'license_id' => $this->license->id,
        'consumed_units' => 999999,
        'limit' => 10000000,
    ]);

    expect($usage->remaining())->toBe(9000001);
});

it('has foreign key relationship to license', function () {
    $usage = LicenseUsage::query()->create([
        'license_id' => $this->license->id,
        'consumed_units' => 50,
        'limit' => 100,
    ]);

    expect($usage->license_id)->toBe($this->license->id)
        ->and($usage->license->id)->toBe($this->license->id)
        ->and($usage->license->key)->toBe($this->license->key);
});

it('can have multiple usages per license', function () {
    $usage1 = LicenseUsage::query()->create([
        'license_id' => $this->license->id,
        'consumed_units' => 50,
        'limit' => 100,
    ]);

    $usage2 = LicenseUsage::query()->create([
        'license_id' => $this->license->id,
        'consumed_units' => 30,
        'limit' => 50,
    ]);

    $usages = $this->license->usages;

    expect($usages->count())->toBe(2)
        ->and($usages->pluck('id')->toArray())->toContain($usage1->id)
        ->and($usages->pluck('id')->toArray())->toContain($usage2->id);
});
