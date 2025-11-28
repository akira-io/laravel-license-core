<?php

declare(strict_types=1);

use Akira\LaravelLicense\Actions\RotateLicenseKeyAction;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseActivation;
use Illuminate\Support\Sleep;

it('can rotate license key', function () {
    $license = License::factory()->create(['key' => 'OLD-KEY-123']);

    $action = new RotateLicenseKeyAction();
    $newKey = $action->handle($license);

    expect($newKey)->toBeString()
        ->and($newKey)->not->toBe('OLD-KEY-123')
        ->and($license->fresh()->key)->toBe($newKey);
});

it('returns new key as string', function () {
    $license = License::factory()->create();

    $action = new RotateLicenseKeyAction();
    $newKey = $action->handle($license);

    expect($newKey)->toBeString()
        ->and($newKey)->not->toBeEmpty();
});

it('updates key in database', function () {
    $license = License::factory()->create(['key' => 'OLD-KEY-123']);

    $action = new RotateLicenseKeyAction();
    $newKey = $action->handle($license);

    expect($license->fresh()->key)->toBe($newKey);

    $this->assertDatabaseHas('licenses', [
        'id' => $license->id,
        'key' => $newKey,
    ]);

    $this->assertDatabaseMissing('licenses', [
        'id' => $license->id,
        'key' => 'OLD-KEY-123',
    ]);
});

it('generates unique keys', function () {
    $license1 = License::factory()->create();
    $license2 = License::factory()->create();

    $action = new RotateLicenseKeyAction();
    $key1 = $action->handle($license1);
    $key2 = $action->handle($license2);

    expect($key1)->not->toBe($key2);
});

it('can rotate same license multiple times', function () {
    $license = License::factory()->create();

    $action = new RotateLicenseKeyAction();
    $key1 = $action->handle($license);
    $key2 = $action->handle($license);
    $key3 = $action->handle($license);

    expect($key1)->not->toBe($key2)
        ->and($key2)->not->toBe($key3)
        ->and($key1)->not->toBe($key3)
        ->and($license->fresh()->key)->toBe($key3);
});

it('preserves other license attributes', function () {
    $license = License::factory()->active()->annual()->create([
        'max_activations' => 5,
    ]);

    $originalStatus = $license->status;
    $originalType = $license->type;
    $originalMax = $license->max_activations;

    $action = new RotateLicenseKeyAction();
    $action->handle($license);

    $fresh = $license->fresh();
    expect($fresh->status)->toBe($originalStatus)
        ->and($fresh->type)->toBe($originalType)
        ->and($fresh->max_activations)->toBe($originalMax);
});

it('is readonly class', function () {
    $reflection = new ReflectionClass(RotateLicenseKeyAction::class);

    expect($reflection->isReadOnly())->toBeTrue();
});

it('is final class', function () {
    $reflection = new ReflectionClass(RotateLicenseKeyAction::class);

    expect($reflection->isFinal())->toBeTrue();
});

it('maintains relationships after rotation', function () {
    $license = License::factory()->create();
    $activation1 = LicenseActivation::factory()->create(['license_id' => $license->id]);
    $activation2 = LicenseActivation::factory()->create(['license_id' => $license->id]);

    $activationIds = [$activation1->id, $activation2->id];

    $action = new RotateLicenseKeyAction();
    $action->handle($license);

    $fresh = $license->fresh();
    expect($fresh->activations->pluck('id')->sort()->values()->toArray())
        ->toEqual(collect($activationIds)->sort()->values()->all())
        ->and($fresh->activations)->toHaveCount(2);
});

it('generates key with expected format', function () {
    $license = License::factory()->create();

    $action = new RotateLicenseKeyAction();
    $newKey = $action->handle($license);

    expect($newKey)->toBeString()
        ->and(mb_strlen($newKey))->toBeGreaterThan(0);
});

it('works with different license types', function () {
    $annual = License::factory()->annual()->create();
    $lifetime = License::factory()->lifetime()->create();
    $trial = License::factory()->trial()->create();

    $action = new RotateLicenseKeyAction();

    $key1 = $action->handle($annual);
    $key2 = $action->handle($lifetime);
    $key3 = $action->handle($trial);

    expect($key1)->toBeString()
        ->and($key2)->toBeString()
        ->and($key3)->toBeString()
        ->and($key1)->not->toBe($key2)
        ->and($key2)->not->toBe($key3);
});

it('updates updated_at timestamp', function () {
    $license = License::factory()->create();
    $oldTimestamp = $license->updated_at;

    Sleep::sleep(1);

    $action = new RotateLicenseKeyAction();
    $action->handle($license);

    expect($license->fresh()->updated_at->isAfter($oldTimestamp))->toBeTrue();
});
