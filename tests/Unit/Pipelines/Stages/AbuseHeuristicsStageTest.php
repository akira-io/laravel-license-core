<?php

declare(strict_types=1);

use Akira\LaravelLicense\Enums\LicenseEventType;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseEvent;
use Akira\LaravelLicense\Pipelines\Stages\AbuseHeuristicsStage;
use Akira\LaravelLicense\ValueObjects\LicenseContext;
use Akira\LaravelLicense\ValueObjects\LicenseKey;

test('passes when license is not loaded', function () {
    $context = new LicenseContext(
        key: LicenseKey::fromString('TEST-1234-5678-90AB'),
        domain: null,
    );

    $stage = new AbuseHeuristicsStage();
    $result = $stage($context);

    expect($result)->toBe($context);
});

test('passes when no recent activations', function () {
    $license = License::factory()->create(['status' => 'active']);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license,
    );

    $stage = new AbuseHeuristicsStage();
    $result = $stage($context);

    expect($result)->toBe($context)
        ->and(LicenseEvent::query()->count())->toBe(0);
});

test('passes when few recent activations', function () {
    $license = License::factory()->create(['status' => 'active']);

    LicenseEvent::factory()->count(5)->create([
        'license_id' => $license->id,
        'type' => LicenseEventType::ACTIVATED->value,
        'created_at' => now()->subMinutes(5),
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license->fresh(),
    );

    $stage = new AbuseHeuristicsStage();
    $result = $stage($context);

    expect($result)->toBe($context)
        ->and(LicenseEvent::query()->where('type', LicenseEventType::ABUSE_DETECTED->value)->count())->toBe(0);
});

test('creates abuse event when many recent activations', function () {
    $license = License::factory()->create(['status' => 'active']);

    LicenseEvent::factory()->count(10)->create([
        'license_id' => $license->id,
        'type' => LicenseEventType::ACTIVATED->value,
        'created_at' => now()->subMinutes(5),
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license->fresh(),
    );

    $stage = new AbuseHeuristicsStage();
    $result = $stage($context);

    expect($result)->toBe($context)
        ->and(LicenseEvent::query()->where('type', LicenseEventType::ABUSE_DETECTED->value)->count())->toBe(1);

    $abuseEvent = LicenseEvent::query()->where('type', LicenseEventType::ABUSE_DETECTED->value)->first();
    expect($abuseEvent->payload)->toHaveKey('activations_last_10min')
        ->and($abuseEvent->payload['activations_last_10min'])->toBe(10);
});

test('ignores old activation events', function () {
    $license = License::factory()->create(['status' => 'active']);

    LicenseEvent::factory()->count(15)->create([
        'license_id' => $license->id,
        'type' => LicenseEventType::ACTIVATED->value,
        'created_at' => now()->subMinutes(15),
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license->fresh(),
    );

    $stage = new AbuseHeuristicsStage();
    $result = $stage($context);

    expect($result)->toBe($context)
        ->and(LicenseEvent::query()->where('type', LicenseEventType::ABUSE_DETECTED->value)->count())->toBe(0);
});

test('counts only activation events within 10 minutes', function () {
    $license = License::factory()->create(['status' => 'active']);

    LicenseEvent::factory()->count(5)->create([
        'license_id' => $license->id,
        'type' => LicenseEventType::ACTIVATED->value,
        'created_at' => now()->subMinutes(5),
    ]);

    LicenseEvent::factory()->count(3)->create([
        'license_id' => $license->id,
        'type' => LicenseEventType::USAGE_CONSUMED->value,
        'created_at' => now()->subMinutes(5),
    ]);

    $context = new LicenseContext(
        key: LicenseKey::fromString($license->key),
        domain: null,
        license: $license->fresh(),
    );

    $stage = new AbuseHeuristicsStage();
    $result = $stage($context);

    expect($result)->toBe($context)
        ->and(LicenseEvent::query()->where('type', LicenseEventType::ABUSE_DETECTED->value)->count())->toBe(0);
});
