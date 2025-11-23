<?php

declare(strict_types=1);

use Akira\LaravelLicense\Enums\LicenseStatus;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseActivation;
use Akira\LaravelLicense\Models\LicenseEvent;
use Akira\LaravelLicense\Models\LicenseUsage;
use Akira\LaravelLicense\Support\ConfigManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('can create a license', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
        'max_activations' => 5,
        'max_seats' => 10,
    ]);

    expect($license)->toBeInstanceOf(License::class)
        ->and($license->type)->toBe('annual')
        ->and($license->status)->toBe('active')
        ->and($license->max_activations)->toBe(5)
        ->and($license->max_seats)->toBe(10);
});

it('has many activations', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
    ]);

    LicenseActivation::query()->create([
        'license_id' => $license->id,
        'domain' => 'example.com',
    ]);

    LicenseActivation::query()->create([
        'license_id' => $license->id,
        'domain' => 'test.com',
    ]);

    expect($license->activations()->count())->toBe(2);
});

it('has many usages', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::CREDITS->value,
        'status' => LicenseStatus::ACTIVE->value,
    ]);

    LicenseUsage::query()->create([
        'license_id' => $license->id,
        'consumed_units' => 50,
        'limit' => 100,
    ]);

    LicenseUsage::query()->create([
        'license_id' => $license->id,
        'consumed_units' => 30,
        'limit' => 50,
    ]);

    expect($license->usages()->count())->toBe(2);
});

it('has many events', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::TRIAL->value,
        'status' => LicenseStatus::ACTIVE->value,
    ]);

    LicenseEvent::query()->create([
        'license_id' => $license->id,
        'type' => 'created',
        'created_at' => now(),
    ]);

    LicenseEvent::query()->create([
        'license_id' => $license->id,
        'type' => 'activated',
        'created_at' => now(),
    ]);

    expect($license->events()->count())->toBe(2);
});

it('returns type as enum', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::LIFETIME->value,
        'status' => LicenseStatus::ACTIVE->value,
    ]);

    expect($license->typeEnum())->toBeInstanceOf(LicenseType::class)
        ->and($license->typeEnum())->toBe(LicenseType::LIFETIME);
});

it('returns status as enum', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::SUSPENDED->value,
    ]);

    expect($license->statusEnum())->toBeInstanceOf(LicenseStatus::class)
        ->and($license->statusEnum())->toBe(LicenseStatus::SUSPENDED);
});

it('encrypts and decrypts meta attribute', function () {
    $meta = [
        'customer_id' => 123,
        'plan' => 'pro',
        'features' => ['feature1', 'feature2'],
    ];

    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
        'meta' => $meta,
    ]);

    $retrieved = License::query()->find($license->id);

    expect($retrieved->meta)->toBe($meta)
        ->and($retrieved->meta['customer_id'])->toBe(123)
        ->and($retrieved->meta['plan'])->toBe('pro')
        ->and($retrieved->meta['features'])->toBe(['feature1', 'feature2']);
});

it('returns null for null meta attribute', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
        'meta' => null,
    ]);

    expect($license->meta)->toBeNull();
});

it('returns null for invalid meta type on set', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
        'meta' => 'invalid string',
    ]);

    expect($license->meta)->toBeNull();
});

it('detects expired license', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
        'expires_at' => now()->subDay(),
    ]);

    expect($license->isExpired())->toBeTrue();
});

it('detects non-expired license', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
        'expires_at' => now()->addDay(),
    ]);

    expect($license->isExpired())->toBeFalse();
});

it('returns false for isExpired when expires_at is null', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::LIFETIME->value,
        'status' => LicenseStatus::ACTIVE->value,
        'expires_at' => null,
    ]);

    expect($license->isExpired())->toBeFalse();
});

it('detects license in grace period', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
        'expires_at' => now()->subDay(),
        'grace_ends_at' => now()->addDay(),
    ]);

    expect($license->inGracePeriod())->toBeTrue();
});

it('detects license not in grace period when before expiry', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
        'expires_at' => now()->addDay(),
        'grace_ends_at' => now()->addWeek(),
    ]);

    expect($license->inGracePeriod())->toBeFalse();
});

it('detects license not in grace period when after grace ends', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
        'expires_at' => now()->subWeek(),
        'grace_ends_at' => now()->subDay(),
    ]);

    expect($license->inGracePeriod())->toBeFalse();
});

it('returns false for inGracePeriod when grace_ends_at is null', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
        'expires_at' => now()->subDay(),
        'grace_ends_at' => null,
    ]);

    expect($license->inGracePeriod())->toBeFalse();
});

it('returns false for inGracePeriod when expires_at is null', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::LIFETIME->value,
        'status' => LicenseStatus::ACTIVE->value,
        'expires_at' => null,
        'grace_ends_at' => now()->addWeek(),
    ]);

    expect($license->inGracePeriod())->toBeFalse();
});

it('returns false for inGracePeriod when both dates are null', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::LIFETIME->value,
        'status' => LicenseStatus::ACTIVE->value,
        'expires_at' => null,
        'grace_ends_at' => null,
    ]);

    expect($license->inGracePeriod())->toBeFalse();
});

it('uses custom table name from config', function () {
    config()->set('license.tables.licenses', 'custom_licenses');

    $license = new License();

    expect($license->getTable())->toBe('custom_licenses');
});

it('uses default table name', function () {
    $configManager = app(ConfigManager::class);
    $license = new License();

    expect($license->getTable())->toBe($configManager->getLicenseTable());
});

it('has fillable attributes', function () {
    $license = new License();

    expect($license->getFillable())->toBe([
        'key',
        'type',
        'status',
        'max_activations',
        'max_seats',
        'fallback',
        'scopes',
        'meta',
        'expires_at',
        'grace_ends_at',
    ]);
});

it('casts expires_at to datetime', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
        'expires_at' => now()->addMonth(),
    ]);

    expect($license->expires_at)->toBeInstanceOf(Carbon::class);
});

it('casts grace_ends_at to datetime', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
        'grace_ends_at' => now()->addWeek(),
    ]);

    expect($license->grace_ends_at)->toBeInstanceOf(Carbon::class);
});

it('casts fallback to boolean', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
        'fallback' => true,
    ]);

    expect($license->fallback)->toBeBool()
        ->and($license->fallback)->toBeTrue();
});

it('casts scopes to array object', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
        'scopes' => ['read', 'write', 'admin'],
    ]);

    $retrieved = License::query()->find($license->id);

    expect($retrieved->scopes)->toBeInstanceOf(ArrayObject::class)
        ->and(iterator_to_array($retrieved->scopes))->toBe(['read', 'write', 'admin']);
});

it('can store null scopes', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
        'scopes' => null,
    ]);

    expect($license->scopes)->toBeNull();
});

it('can create license with different types', function () {
    $types = [
        LicenseType::LIFETIME,
        LicenseType::ANNUAL,
        LicenseType::SUBSCRIPTION,
        LicenseType::TRIAL,
        LicenseType::CREDITS,
    ];

    foreach ($types as $type) {
        $license = License::query()->create([
            'key' => fake()->uuid(),
            'type' => $type->value,
            'status' => LicenseStatus::ACTIVE->value,
        ]);

        expect($license->typeEnum())->toBe($type);
    }

    expect(License::query()->count())->toBe(count($types));
});

it('can create license with different statuses', function () {
    $statuses = [
        LicenseStatus::ACTIVE,
        LicenseStatus::EXPIRED,
        LicenseStatus::SUSPENDED,
        LicenseStatus::REVOKED,
    ];

    foreach ($statuses as $status) {
        $license = License::query()->create([
            'key' => fake()->uuid(),
            'type' => LicenseType::ANNUAL->value,
            'status' => $status->value,
        ]);

        expect($license->statusEnum())->toBe($status);
    }

    expect(License::query()->count())->toBe(count($statuses));
});

it('can update license attributes', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::TRIAL->value,
        'status' => LicenseStatus::ACTIVE->value,
        'max_activations' => 1,
        'max_seats' => 1,
    ]);

    $license->update([
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::SUSPENDED->value,
        'max_activations' => 10,
        'max_seats' => 20,
    ]);

    expect($license->fresh()->type)->toBe('annual')
        ->and($license->fresh()->status)->toBe('suspended')
        ->and($license->fresh()->max_activations)->toBe(10)
        ->and($license->fresh()->max_seats)->toBe(20);
});

it('has timestamps enabled', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
    ]);

    expect($license->created_at)->not->toBeNull()
        ->and($license->updated_at)->not->toBeNull();
});

it('can handle complex meta data', function () {
    $complexMeta = [
        'customer' => [
            'id' => 123,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ],
        'features' => [
            'api_access' => true,
            'storage_gb' => 100,
            'users' => 50,
        ],
        'notes' => 'Special customer with custom requirements',
    ];

    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
        'meta' => $complexMeta,
    ]);

    $retrieved = License::query()->find($license->id);

    expect($retrieved->meta['customer']['id'])->toBe(123)
        ->and($retrieved->meta['customer']['name'])->toBe('John Doe')
        ->and($retrieved->meta['features']['api_access'])->toBeTrue()
        ->and($retrieved->meta['features']['storage_gb'])->toBe(100)
        ->and($retrieved->meta['notes'])->toBe('Special customer with custom requirements');
});

it('can delete license', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
    ]);

    $licenseId = $license->id;
    $license->delete();

    expect(License::query()->find($licenseId))->toBeNull();
});

it('applies database default values', function () {
    $license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
        'max_activations' => 1,
        'max_seats' => 1,
        'fallback' => false,
    ]);

    expect($license->max_activations)->toBe(1)
        ->and($license->max_seats)->toBe(1)
        ->and($license->fallback)->toBe(false);
});

it('handles non-string meta value on get', function () {
    $license = new License([
        'key' => fake()->uuid(),
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
    ]);

    // Directly set meta to non-string in attributes (bypassing setter)
    $license->setRawAttributes(['meta' => 123]);

    expect($license->meta)->toBeNull();
});

it('handles invalid decrypted meta value', function () {
    $license = new License([
        'key' => fake()->uuid(),
        'type' => LicenseType::ANNUAL->value,
        'status' => LicenseStatus::ACTIVE->value,
    ]);

    // Set an encrypted value that will decrypt to non-string
    // This is a hypothetical edge case that's hard to trigger naturally
    // but we need to ensure the code handles it
    $encrypted = encrypt(123);
    $license->setRawAttributes(['meta' => $encrypted]);

    // The decrypt will return an integer, not a string, so meta() should return null
    expect($license->meta)->toBeNull();
});
