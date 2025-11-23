<?php

declare(strict_types=1);

use Akira\LaravelLicense\Enums\LicenseEventType;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseEvent;
use Akira\LaravelLicense\Support\ConfigManager;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->license = License::query()->create([
        'key' => fake()->uuid(),
        'type' => 'standard',
        'status' => 'active',
        'max_activations' => 5,
        'max_seats' => 10,
    ]);
});

it('can create license event', function () {
    $event = LicenseEvent::query()->create([
        'license_id' => $this->license->id,
        'type' => LicenseEventType::CREATED->value,
        'payload' => ['data' => 'test'],
        'created_at' => now(),
    ]);

    expect($event)->toBeInstanceOf(LicenseEvent::class)
        ->and($event->license_id)->toBe($this->license->id)
        ->and($event->type)->toBe('created')
        ->and($event->payload)->toBeInstanceOf(ArrayObject::class)
        ->and($event->payload['data'])->toBe('test');
});

it('belongs to a license', function () {
    $event = LicenseEvent::query()->create([
        'license_id' => $this->license->id,
        'type' => LicenseEventType::ACTIVATED->value,
        'payload' => null,
        'created_at' => now(),
    ]);

    expect($event->license)->toBeInstanceOf(License::class)
        ->and($event->license->id)->toBe($this->license->id);
});

it('has timestamps disabled', function () {
    $event = new LicenseEvent();

    expect($event->timestamps)->toBe(false);
});

it('casts payload to array object', function () {
    $event = LicenseEvent::query()->create([
        'license_id' => $this->license->id,
        'type' => LicenseEventType::USAGE_CONSUMED->value,
        'payload' => ['consumed' => 10, 'limit' => 100],
        'created_at' => now(),
    ]);

    expect($event->payload)->toBeInstanceOf(ArrayObject::class)
        ->and($event->payload['consumed'])->toBe(10)
        ->and($event->payload['limit'])->toBe(100);
});

it('casts created_at to datetime', function () {
    $now = now();
    $event = LicenseEvent::query()->create([
        'license_id' => $this->license->id,
        'type' => LicenseEventType::CREATED->value,
        'payload' => null,
        'created_at' => $now,
    ]);

    expect($event->created_at)->toBeInstanceOf(Carbon::class)
        ->and($event->created_at->toDateTimeString())->toBe($now->toDateTimeString());
});

it('can store null payload', function () {
    $event = LicenseEvent::query()->create([
        'license_id' => $this->license->id,
        'type' => LicenseEventType::REVOKED->value,
        'payload' => null,
        'created_at' => now(),
    ]);

    expect($event->payload)->toBeNull();
});

it('can store complex payload data', function () {
    $payload = [
        'user_id' => 123,
        'ip_address' => '192.168.1.1',
        'metadata' => [
            'browser' => 'Chrome',
            'os' => 'Linux',
        ],
    ];

    $event = LicenseEvent::query()->create([
        'license_id' => $this->license->id,
        'type' => LicenseEventType::ACTIVATED->value,
        'payload' => $payload,
        'created_at' => now(),
    ]);

    expect($event->payload['user_id'])->toBe(123)
        ->and($event->payload['ip_address'])->toBe('192.168.1.1')
        ->and($event->payload['metadata']['browser'])->toBe('Chrome')
        ->and($event->payload['metadata']['os'])->toBe('Linux');
});

it('uses custom table name from config', function () {
    config()->set('license.tables.events', 'custom_events');

    $event = new LicenseEvent();

    expect($event->getTable())->toBe('custom_events');
});

it('uses default table name', function () {
    $configManager = app(ConfigManager::class);
    $event = new LicenseEvent();

    expect($event->getTable())->toBe($configManager->getEventsTable());
});

it('has fillable attributes', function () {
    $event = new LicenseEvent();

    expect($event->getFillable())->toBe([
        'license_id',
        'type',
        'payload',
        'created_at',
    ]);
});

it('can create event with different types', function () {
    $types = [
        LicenseEventType::CREATED,
        LicenseEventType::ACTIVATED,
        LicenseEventType::DEACTIVATED,
        LicenseEventType::ROTATED,
        LicenseEventType::REVOKED,
        LicenseEventType::USAGE_CONSUMED,
        LicenseEventType::EXPIRED,
        LicenseEventType::ABUSE_DETECTED,
    ];

    foreach ($types as $type) {
        $event = LicenseEvent::query()->create([
            'license_id' => $this->license->id,
            'type' => $type->value,
            'payload' => ['type' => $type->name],
            'created_at' => now(),
        ]);

        expect($event->type)->toBe($type->value);
    }

    expect(LicenseEvent::query()->count())->toBe(count($types));
});

it('can have multiple events per license', function () {
    $event1 = LicenseEvent::query()->create([
        'license_id' => $this->license->id,
        'type' => LicenseEventType::CREATED->value,
        'payload' => null,
        'created_at' => now(),
    ]);

    $event2 = LicenseEvent::query()->create([
        'license_id' => $this->license->id,
        'type' => LicenseEventType::ACTIVATED->value,
        'payload' => null,
        'created_at' => now()->addMinute(),
    ]);

    $events = $this->license->events;

    expect($events->count())->toBe(2)
        ->and($events->pluck('id')->toArray())->toContain($event1->id)
        ->and($events->pluck('id')->toArray())->toContain($event2->id);
});

it('has foreign key relationship to license', function () {
    $event = LicenseEvent::query()->create([
        'license_id' => $this->license->id,
        'type' => LicenseEventType::CREATED->value,
        'payload' => null,
        'created_at' => now(),
    ]);

    expect($event->license_id)->toBe($this->license->id)
        ->and($event->license->id)->toBe($this->license->id)
        ->and($event->license->key)->toBe($this->license->key);
});

it('has correct casts defined', function () {
    $event = new LicenseEvent();
    $casts = $event->getCasts();

    expect($casts)->toHaveKey('payload')
        ->and($casts)->toHaveKey('created_at')
        ->and($casts['created_at'])->toBe('datetime');
});

it('can query events by type', function () {
    LicenseEvent::query()->create([
        'license_id' => $this->license->id,
        'type' => LicenseEventType::CREATED->value,
        'payload' => null,
        'created_at' => now(),
    ]);

    LicenseEvent::query()->create([
        'license_id' => $this->license->id,
        'type' => LicenseEventType::ACTIVATED->value,
        'payload' => null,
        'created_at' => now(),
    ]);

    LicenseEvent::query()->create([
        'license_id' => $this->license->id,
        'type' => LicenseEventType::ACTIVATED->value,
        'payload' => null,
        'created_at' => now(),
    ]);

    $activatedEvents = LicenseEvent::query()->where('type', LicenseEventType::ACTIVATED->value)->get();

    expect($activatedEvents->count())->toBe(2);
});

it('can order events by created_at', function () {
    $firstEvent = LicenseEvent::query()->create([
        'license_id' => $this->license->id,
        'type' => LicenseEventType::CREATED->value,
        'payload' => null,
        'created_at' => now()->subHours(2),
    ]);

    $secondEvent = LicenseEvent::query()->create([
        'license_id' => $this->license->id,
        'type' => LicenseEventType::ACTIVATED->value,
        'payload' => null,
        'created_at' => now()->subHour(),
    ]);

    $thirdEvent = LicenseEvent::query()->create([
        'license_id' => $this->license->id,
        'type' => LicenseEventType::USAGE_CONSUMED->value,
        'payload' => null,
        'created_at' => now(),
    ]);

    $events = LicenseEvent::query()->oldest()->get();

    expect($events->first()->id)->toBe($firstEvent->id)
        ->and($events->last()->id)->toBe($thirdEvent->id);
});

it('preserves payload data after retrieval', function () {
    $originalPayload = [
        'action' => 'test',
        'values' => [1, 2, 3],
        'nested' => ['key' => 'value'],
    ];

    $event = LicenseEvent::query()->create([
        'license_id' => $this->license->id,
        'type' => LicenseEventType::CREATED->value,
        'payload' => $originalPayload,
        'created_at' => now(),
    ]);

    $retrieved = LicenseEvent::query()->find($event->id);

    expect($retrieved->payload['action'])->toBe('test')
        ->and($retrieved->payload['values'])->toBe([1, 2, 3])
        ->and($retrieved->payload['nested']['key'])->toBe('value');
});
