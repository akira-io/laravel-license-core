<?php

declare(strict_types=1);

use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseActivation;
use Akira\LaravelLicense\Models\LicenseEvent;
use Akira\LaravelLicense\Models\LicenseUsage;

it('can create license using factory', function () {
    $license = License::factory()->create();

    expect($license)->toBeInstanceOf(License::class)
        ->and($license->key)->not->toBeNull()
        ->and($license->type)->not->toBeNull()
        ->and($license->status)->not->toBeNull();
});

it('can create license with active state', function () {
    $license = License::factory()->active()->create();

    expect($license->status)->toBe('active');
});

it('can create license with expired state', function () {
    $license = License::factory()->expired()->create();

    expect($license->status)->toBe('expired')
        ->and($license->expires_at)->not->toBeNull();
});

it('can create license with suspended state', function () {
    $license = License::factory()->suspended()->create();

    expect($license->status)->toBe('suspended');
});

it('can create license with revoked state', function () {
    $license = License::factory()->revoked()->create();

    expect($license->status)->toBe('revoked');
});

it('can create lifetime license', function () {
    $license = License::factory()->lifetime()->create();

    expect($license->type)->toBe('lifetime')
        ->and($license->expires_at)->toBeNull();
});

it('can create annual license', function () {
    $license = License::factory()->annual()->create();

    expect($license->type)->toBe('annual')
        ->and($license->expires_at)->not->toBeNull();
});

it('can create trial license', function () {
    $license = License::factory()->trial()->create();

    expect($license->type)->toBe('trial')
        ->and($license->expires_at)->not->toBeNull();
});

it('can create license with grace period', function () {
    $license = License::factory()->withGracePeriod()->create();

    expect($license->expires_at)->not->toBeNull()
        ->and($license->grace_ends_at)->not->toBeNull();
});

it('can create license with meta', function () {
    $meta = ['customer_id' => 123, 'plan' => 'pro'];
    $license = License::factory()->withMeta($meta)->create();

    expect($license->meta)->toBe($meta);
});

it('can create activation using factory', function () {
    $activation = LicenseActivation::factory()->create();

    expect($activation)->toBeInstanceOf(LicenseActivation::class)
        ->and($activation->license_id)->not->toBeNull();
});

it('can create activation for specific license', function () {
    $license = License::factory()->create();
    $activation = LicenseActivation::factory()->forLicense($license)->create();

    expect($activation->license_id)->toBe($license->id);
});

it('can create activation with domain', function () {
    $activation = LicenseActivation::factory()->withDomain('example.com')->create();

    expect($activation->domain)->toBe('example.com');
});

it('can create activation with machine hash', function () {
    $activation = LicenseActivation::factory()->withMachineHash('abc123')->create();

    expect($activation->machine_hash)->toBe('abc123');
});

it('can create activation with ip', function () {
    $activation = LicenseActivation::factory()->withIp('192.168.1.1')->create();

    expect($activation->ip)->toBe('192.168.1.1');
});

it('can create activation with ipv6', function () {
    $activation = LicenseActivation::factory()->withIpv6()->create();

    expect($activation->ip)->not->toBeNull()
        ->and($activation->ip)->toContain(':');
});

it('can create event using factory', function () {
    $event = LicenseEvent::factory()->create();

    expect($event)->toBeInstanceOf(LicenseEvent::class)
        ->and($event->license_id)->not->toBeNull()
        ->and($event->type)->not->toBeNull();
});

it('can create event for specific license', function () {
    $license = License::factory()->create();
    $event = LicenseEvent::factory()->forLicense($license)->create();

    expect($event->license_id)->toBe($license->id);
});

it('can create created event', function () {
    $event = LicenseEvent::factory()->created()->create();

    expect($event->type)->toBe('created');
});

it('can create activated event', function () {
    $event = LicenseEvent::factory()->activated()->create();

    expect($event->type)->toBe('activated');
});

it('can create deactivated event', function () {
    $event = LicenseEvent::factory()->deactivated()->create();

    expect($event->type)->toBe('deactivated');
});

it('can create rotated event', function () {
    $event = LicenseEvent::factory()->rotated()->create();

    expect($event->type)->toBe('rotated');
});

it('can create revoked event', function () {
    $event = LicenseEvent::factory()->revoked()->create();

    expect($event->type)->toBe('revoked');
});

it('can create expired event', function () {
    $event = LicenseEvent::factory()->expired()->create();

    expect($event->type)->toBe('expired');
});

it('can create event with payload', function () {
    $payload = ['action' => 'test', 'data' => 123];
    $event = LicenseEvent::factory()->withPayload($payload)->create();

    expect($event->payload)->not->toBeNull();
});

it('can create event without payload', function () {
    $event = LicenseEvent::factory()->withoutPayload()->create();

    expect($event->payload)->toBeNull();
});

it('can create usage using factory', function () {
    $usage = LicenseUsage::factory()->create();

    expect($usage)->toBeInstanceOf(LicenseUsage::class)
        ->and($usage->license_id)->not->toBeNull()
        ->and($usage->consumed_units)->toBeGreaterThanOrEqual(0)
        ->and($usage->limit)->toBeGreaterThan(0);
});

it('can create usage for specific license', function () {
    $license = License::factory()->create();
    $usage = LicenseUsage::factory()->forLicense($license)->create();

    expect($usage->license_id)->toBe($license->id);
});

it('can create usage with specific limit', function () {
    $usage = LicenseUsage::factory()->withLimit(1000)->create();

    expect($usage->limit)->toBe(1000);
});

it('can create usage with specific consumed units', function () {
    $usage = LicenseUsage::factory()->withConsumed(50)->create();

    expect($usage->consumed_units)->toBe(50);
});

it('can create depleted usage', function () {
    $usage = LicenseUsage::factory()->depleted()->create();

    expect($usage->consumed_units)->toBe($usage->limit)
        ->and($usage->remaining())->toBe(0);
});

it('can create over limit usage', function () {
    $usage = LicenseUsage::factory()->overLimit()->create();

    expect($usage->consumed_units)->toBeGreaterThan($usage->limit)
        ->and($usage->remaining())->toBe(0);
});

it('can create fresh usage', function () {
    $usage = LicenseUsage::factory()->fresh()->create();

    expect($usage->consumed_units)->toBe(0)
        ->and($usage->remaining())->toBe($usage->limit);
});

it('can create multiple models using factories', function () {
    $licenses = License::factory()->count(3)->create();

    expect($licenses)->toHaveCount(3);
});

it('factories work with relationships', function () {
    $license = License::factory()
        ->has(LicenseActivation::factory()->count(2), 'activations')
        ->has(LicenseEvent::factory()->count(3), 'events')
        ->has(LicenseUsage::factory()->count(1), 'usages')
        ->create();

    expect($license->activations)->toHaveCount(2)
        ->and($license->events)->toHaveCount(3)
        ->and($license->usages)->toHaveCount(1);
});
