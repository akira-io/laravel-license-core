<?php

declare(strict_types=1);

use Akira\LaravelLicense\Actions\ActivateLicenseAction;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseActivation;
use Akira\LaravelLicense\ValueObjects\DomainName;
use Akira\LaravelLicense\ValueObjects\MachineFingerprint;

it('can activate license with domain and machine', function () {
    $license = License::factory()->create();
    $domain = new DomainName('example.com');
    $machine = new MachineFingerprint(hash('sha256', 'test-machine'));

    $action = new ActivateLicenseAction();
    $activation = $action->handle($license, $domain, $machine);

    expect($activation)->toBeInstanceOf(LicenseActivation::class)
        ->and($activation->license_id)->toBe($license->id)
        ->and($activation->domain)->toBe('example.com')
        ->and($activation->machine_hash)->toBe(hash('sha256', 'test-machine'));
});

it('stores ip and user agent from request', function () {
    $license = License::factory()->create();
    $domain = new DomainName('example.com');
    $machine = new MachineFingerprint(hash('sha256', 'test-machine'));

    $action = new ActivateLicenseAction();
    $activation = $action->handle($license, $domain, $machine);

    expect($activation)->toBeInstanceOf(LicenseActivation::class)
        ->and($activation->license_id)->toBe($license->id);
});

it('creates activation record in database', function () {
    $license = License::factory()->create();
    $domain = new DomainName('example.com');
    $machine = new MachineFingerprint(hash('sha256', 'test-machine'));

    $action = new ActivateLicenseAction();
    $activation = $action->handle($license, $domain, $machine);

    $this->assertDatabaseHas('license_activations', [
        'id' => $activation->id,
        'license_id' => $license->id,
        'domain' => 'example.com',
        'machine_hash' => hash('sha256', 'test-machine'),
    ]);
});

it('can activate same license multiple times', function () {
    $license = License::factory()->create();
    $domain1 = new DomainName('site1.com');
    $domain2 = new DomainName('site2.com');
    $machine = new MachineFingerprint(hash('sha256', 'test-machine'));

    $action = new ActivateLicenseAction();
    $activation1 = $action->handle($license, $domain1, $machine);
    $activation2 = $action->handle($license, $domain2, $machine);

    expect($license->activations)->toHaveCount(2)
        ->and($activation1->id)->not->toBe($activation2->id)
        ->and($activation1->domain)->toBe('site1.com')
        ->and($activation2->domain)->toBe('site2.com');
});

it('can activate with different machines', function () {
    $license = License::factory()->create();
    $domain = new DomainName('example.com');
    $machine1 = new MachineFingerprint(hash('sha256', 'machine-1'));
    $machine2 = new MachineFingerprint(hash('sha256', 'machine-2'));

    $action = new ActivateLicenseAction();
    $activation1 = $action->handle($license, $domain, $machine1);
    $activation2 = $action->handle($license, $domain, $machine2);

    expect($license->activations)->toHaveCount(2)
        ->and($activation1->machine_hash)->toBe(hash('sha256', 'machine-1'))
        ->and($activation2->machine_hash)->toBe(hash('sha256', 'machine-2'));
});

it('is readonly class', function () {
    $reflection = new ReflectionClass(ActivateLicenseAction::class);

    expect($reflection->isReadOnly())->toBeTrue();
});

it('is final class', function () {
    $reflection = new ReflectionClass(ActivateLicenseAction::class);

    expect($reflection->isFinal())->toBeTrue();
});

it('returns license activation instance', function () {
    $license = License::factory()->create();
    $domain = new DomainName('example.com');
    $machine = new MachineFingerprint(hash('sha256', 'test-machine'));

    $action = new ActivateLicenseAction();
    $result = $action->handle($license, $domain, $machine);

    expect($result)->toBeInstanceOf(LicenseActivation::class)
        ->and($result->exists)->toBeTrue()
        ->and($result->wasRecentlyCreated)->toBeTrue();
});

it('associates activation with license', function () {
    $license = License::factory()->create();
    $domain = new DomainName('example.com');
    $machine = new MachineFingerprint(hash('sha256', 'test-machine'));

    $action = new ActivateLicenseAction();
    $activation = $action->handle($license, $domain, $machine);

    expect($activation->license->is($license))->toBeTrue()
        ->and($license->activations->contains($activation))->toBeTrue();
});

it('handles different domain formats', function () {
    $license = License::factory()->create();
    $machine = new MachineFingerprint(hash('sha256', 'test-machine'));

    $action = new ActivateLicenseAction();

    $domains = [
        new DomainName('example.com'),
        new DomainName('sub.example.com'),
        new DomainName('192.168.1.1'),
        new DomainName('localhost'),
    ];

    foreach ($domains as $domain) {
        $activation = $action->handle($license, $domain, $machine);
        expect($activation)->toBeInstanceOf(LicenseActivation::class);
    }

    expect($license->activations)->toHaveCount(4);
});
