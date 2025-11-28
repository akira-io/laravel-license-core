<?php

declare(strict_types=1);

use Akira\LaravelLicense\Actions\ValidateUsageAction;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Pipelines\LicenseUsageValidationPipeline;
use Akira\LaravelLicense\ValueObjects\DomainName;
use Akira\LaravelLicense\ValueObjects\LicenseKey;
use Akira\LaravelLicense\ValueObjects\MachineFingerprint;

test('handle returns context with all parameters', function () {
    $license = License::factory()->create(['status' => 'active']);
    $key = LicenseKey::fromString($license->key);
    $machine = new MachineFingerprint('test-machine-123');
    $domain = new DomainName('example.com');

    $pipeline = app(LicenseUsageValidationPipeline::class);
    $action = new ValidateUsageAction($pipeline);

    $result = $action->handle($key, $machine, $domain);

    expect($result->key)->toBe($key)
        ->and($result->machineFingerprint)->toBe($machine)
        ->and($result->domain)->toBe($domain)
        ->and($result->license)->not->toBeNull();
});

test('handle returns context with null domain', function () {
    $license = License::factory()->create(['status' => 'active']);
    $key = LicenseKey::fromString($license->key);
    $machine = new MachineFingerprint('test-machine-123');

    $pipeline = app(LicenseUsageValidationPipeline::class);
    $action = new ValidateUsageAction($pipeline);

    $result = $action->handle($key, $machine, null);

    expect($result->key)->toBe($key)
        ->and($result->machineFingerprint)->toBe($machine)
        ->and($result->domain)->toBeNull()
        ->and($result->license)->not->toBeNull();
});
