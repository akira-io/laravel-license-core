<?php

declare(strict_types=1);

use Akira\LaravelLicense\Actions\ValidateUpdateAction;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Pipelines\LicenseUpdateValidationPipeline;
use Akira\LaravelLicense\ValueObjects\DomainName;
use Akira\LaravelLicense\ValueObjects\LicenseKey;
use Akira\LaravelLicense\ValueObjects\MachineFingerprint;

test('handle returns context with release date', function () {
    $expiryDate = now()->addYear();
    $license = License::factory()->create([
        'status' => 'active',
        'type' => LicenseType::SUBSCRIPTION->value,
        'expires_at' => $expiryDate,
    ]);

    $key = LicenseKey::fromString($license->key);
    $releaseDate = now()->subMonth()->toDateTimeString();

    $pipeline = app(LicenseUpdateValidationPipeline::class);
    $action = new ValidateUpdateAction($pipeline);

    $result = $action->handle($key, $releaseDate);

    expect($result->key)->toBe($key)
        ->and($result->releaseDate)->not->toBeNull()
        ->and($result->license)->not->toBeNull();
});

test('handle returns context with domain and machine', function () {
    $expiryDate = now()->addYear();
    $license = License::factory()->create([
        'status' => 'active',
        'type' => LicenseType::SUBSCRIPTION->value,
        'expires_at' => $expiryDate,
    ]);

    $key = LicenseKey::fromString($license->key);
    $releaseDate = now()->subMonth()->toDateTimeString();
    $domain = new DomainName('example.com');
    $machine = new MachineFingerprint('test-machine');

    $pipeline = app(LicenseUpdateValidationPipeline::class);
    $action = new ValidateUpdateAction($pipeline);

    $result = $action->handle($key, $releaseDate, $domain, $machine);

    expect($result->key)->toBe($key)
        ->and($result->releaseDate)->not->toBeNull()
        ->and($result->domain)->toBe($domain)
        ->and($result->machineFingerprint)->toBe($machine)
        ->and($result->license)->not->toBeNull();
});
