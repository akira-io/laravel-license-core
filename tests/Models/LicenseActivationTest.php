<?php

declare(strict_types=1);

use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseActivation;
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

it('can create license activation', function () {
    $activation = LicenseActivation::query()->create([
        'license_id' => $this->license->id,
        'domain' => 'example.com',
        'machine_hash' => 'abc123',
        'ip' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0',
    ]);

    expect($activation)->toBeInstanceOf(LicenseActivation::class)
        ->and($activation->license_id)->toBe($this->license->id)
        ->and($activation->domain)->toBe('example.com')
        ->and($activation->machine_hash)->toBe('abc123')
        ->and($activation->ip)->toBe('192.168.1.1')
        ->and($activation->user_agent)->toBe('Mozilla/5.0');
});

it('belongs to a license', function () {
    $activation = LicenseActivation::query()->create([
        'license_id' => $this->license->id,
        'domain' => 'test.com',
        'machine_hash' => 'hash123',
        'ip' => '10.0.0.1',
        'user_agent' => 'Chrome',
    ]);

    expect($activation->license)->toBeInstanceOf(License::class)
        ->and($activation->license->id)->toBe($this->license->id);
});

it('can create activation with null domain', function () {
    $activation = LicenseActivation::query()->create([
        'license_id' => $this->license->id,
        'domain' => null,
        'machine_hash' => 'hash456',
        'ip' => '192.168.1.1',
        'user_agent' => 'Firefox',
    ]);

    expect($activation->domain)->toBeNull()
        ->and($activation->machine_hash)->toBe('hash456');
});

it('can create activation with null machine_hash', function () {
    $activation = LicenseActivation::query()->create([
        'license_id' => $this->license->id,
        'domain' => 'example.com',
        'machine_hash' => null,
        'ip' => '192.168.1.1',
        'user_agent' => 'Safari',
    ]);

    expect($activation->machine_hash)->toBeNull()
        ->and($activation->domain)->toBe('example.com');
});

it('can create activation with null ip', function () {
    $activation = LicenseActivation::query()->create([
        'license_id' => $this->license->id,
        'domain' => 'example.com',
        'machine_hash' => 'hash789',
        'ip' => null,
        'user_agent' => 'Edge',
    ]);

    expect($activation->ip)->toBeNull()
        ->and($activation->machine_hash)->toBe('hash789');
});

it('can create activation with null user_agent', function () {
    $activation = LicenseActivation::query()->create([
        'license_id' => $this->license->id,
        'domain' => 'example.com',
        'machine_hash' => 'hash000',
        'ip' => '192.168.1.1',
        'user_agent' => null,
    ]);

    expect($activation->user_agent)->toBeNull()
        ->and($activation->ip)->toBe('192.168.1.1');
});

it('uses custom table name from config', function () {
    config()->set('license.tables.activations', 'custom_activations');

    $activation = new LicenseActivation();

    expect($activation->getTable())->toBe('custom_activations');
});

it('uses default table name', function () {
    $configManager = app(ConfigManager::class);
    $activation = new LicenseActivation();

    expect($activation->getTable())->toBe($configManager->getActivationsTable());
});

it('has fillable attributes', function () {
    $activation = new LicenseActivation();

    expect($activation->getFillable())->toBe([
        'license_id',
        'domain',
        'machine_hash',
        'ip',
        'user_agent',
    ]);
});

it('has timestamps enabled by default', function () {
    $activation = LicenseActivation::query()->create([
        'license_id' => $this->license->id,
        'domain' => 'example.com',
        'machine_hash' => 'hash111',
        'ip' => '192.168.1.1',
        'user_agent' => 'Chrome',
    ]);

    expect($activation->created_at)->not->toBeNull()
        ->and($activation->updated_at)->not->toBeNull();
});

it('can update activation fields', function () {
    $activation = LicenseActivation::query()->create([
        'license_id' => $this->license->id,
        'domain' => 'old-domain.com',
        'machine_hash' => 'old-hash',
        'ip' => '192.168.1.1',
        'user_agent' => 'Old Agent',
    ]);

    $activation->update([
        'domain' => 'new-domain.com',
        'machine_hash' => 'new-hash',
        'ip' => '10.0.0.1',
        'user_agent' => 'New Agent',
    ]);

    expect($activation->fresh()->domain)->toBe('new-domain.com')
        ->and($activation->fresh()->machine_hash)->toBe('new-hash')
        ->and($activation->fresh()->ip)->toBe('10.0.0.1')
        ->and($activation->fresh()->user_agent)->toBe('New Agent');
});

it('can have multiple activations per license', function () {
    $activation1 = LicenseActivation::query()->create([
        'license_id' => $this->license->id,
        'domain' => 'domain1.com',
        'machine_hash' => 'hash1',
        'ip' => '192.168.1.1',
        'user_agent' => 'Agent1',
    ]);

    $activation2 = LicenseActivation::query()->create([
        'license_id' => $this->license->id,
        'domain' => 'domain2.com',
        'machine_hash' => 'hash2',
        'ip' => '192.168.1.2',
        'user_agent' => 'Agent2',
    ]);

    $activations = $this->license->activations;

    expect($activations->count())->toBe(2)
        ->and($activations->pluck('id')->toArray())->toContain($activation1->id)
        ->and($activations->pluck('id')->toArray())->toContain($activation2->id);
});

it('has foreign key relationship to license', function () {
    $activation = LicenseActivation::query()->create([
        'license_id' => $this->license->id,
        'domain' => 'example.com',
        'machine_hash' => 'hash123',
        'ip' => '192.168.1.1',
        'user_agent' => 'Chrome',
    ]);

    expect($activation->license_id)->toBe($this->license->id)
        ->and($activation->license->id)->toBe($this->license->id)
        ->and($activation->license->key)->toBe($this->license->key);
});

it('can query activations by domain', function () {
    LicenseActivation::query()->create([
        'license_id' => $this->license->id,
        'domain' => 'example.com',
        'machine_hash' => 'hash1',
        'ip' => '192.168.1.1',
        'user_agent' => 'Chrome',
    ]);

    LicenseActivation::query()->create([
        'license_id' => $this->license->id,
        'domain' => 'example.com',
        'machine_hash' => 'hash2',
        'ip' => '192.168.1.2',
        'user_agent' => 'Firefox',
    ]);

    LicenseActivation::query()->create([
        'license_id' => $this->license->id,
        'domain' => 'other.com',
        'machine_hash' => 'hash3',
        'ip' => '192.168.1.3',
        'user_agent' => 'Safari',
    ]);

    $activations = LicenseActivation::query()->where('domain', 'example.com')->get();

    expect($activations->count())->toBe(2);
});

it('can query activations by machine_hash', function () {
    $targetHash = 'unique-hash-123';

    LicenseActivation::query()->create([
        'license_id' => $this->license->id,
        'domain' => 'example.com',
        'machine_hash' => $targetHash,
        'ip' => '192.168.1.1',
        'user_agent' => 'Chrome',
    ]);

    LicenseActivation::query()->create([
        'license_id' => $this->license->id,
        'domain' => 'example.com',
        'machine_hash' => 'other-hash',
        'ip' => '192.168.1.2',
        'user_agent' => 'Firefox',
    ]);

    $activation = LicenseActivation::query()->where('machine_hash', $targetHash)->first();

    expect($activation)->not->toBeNull()
        ->and($activation->machine_hash)->toBe($targetHash);
});

it('can query activations by ip', function () {
    $targetIp = '10.20.30.40';

    LicenseActivation::query()->create([
        'license_id' => $this->license->id,
        'domain' => 'example.com',
        'machine_hash' => 'hash1',
        'ip' => $targetIp,
        'user_agent' => 'Chrome',
    ]);

    LicenseActivation::query()->create([
        'license_id' => $this->license->id,
        'domain' => 'example.com',
        'machine_hash' => 'hash2',
        'ip' => '192.168.1.1',
        'user_agent' => 'Firefox',
    ]);

    $activation = LicenseActivation::query()->where('ip', $targetIp)->first();

    expect($activation)->not->toBeNull()
        ->and($activation->ip)->toBe($targetIp);
});

it('can store IPv6 addresses', function () {
    $ipv6 = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';

    $activation = LicenseActivation::query()->create([
        'license_id' => $this->license->id,
        'domain' => 'example.com',
        'machine_hash' => 'hash123',
        'ip' => $ipv6,
        'user_agent' => 'Chrome',
    ]);

    expect($activation->ip)->toBe($ipv6);
});

it('can store long user agent strings', function () {
    $longUserAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';

    $activation = LicenseActivation::query()->create([
        'license_id' => $this->license->id,
        'domain' => 'example.com',
        'machine_hash' => 'hash123',
        'ip' => '192.168.1.1',
        'user_agent' => $longUserAgent,
    ]);

    expect($activation->user_agent)->toBe($longUserAgent);
});

it('can delete activation', function () {
    $activation = LicenseActivation::query()->create([
        'license_id' => $this->license->id,
        'domain' => 'example.com',
        'machine_hash' => 'hash123',
        'ip' => '192.168.1.1',
        'user_agent' => 'Chrome',
    ]);

    $activationId = $activation->id;
    $activation->delete();

    expect(LicenseActivation::query()->find($activationId))->toBeNull();
});

it('can create activation with all nullable fields as null', function () {
    $activation = LicenseActivation::query()->create([
        'license_id' => $this->license->id,
        'domain' => null,
        'machine_hash' => null,
        'ip' => null,
        'user_agent' => null,
    ]);

    expect($activation->license_id)->toBe($this->license->id)
        ->and($activation->domain)->toBeNull()
        ->and($activation->machine_hash)->toBeNull()
        ->and($activation->ip)->toBeNull()
        ->and($activation->user_agent)->toBeNull();
});
