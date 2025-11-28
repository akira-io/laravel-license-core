<?php

declare(strict_types=1);

use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Pipelines\LicenseUsageValidationPipeline;
use Akira\LaravelLicense\ValueObjects\LicenseContext;
use Akira\LaravelLicense\ValueObjects\LicenseKey;
use Akira\LaravelLicense\ValueObjects\MachineFingerprint;

test('pipeline processes context through all stages', function () {
    $license = License::factory()->create(['status' => 'active']);
    $key = LicenseKey::fromString($license->key);
    $machine = new MachineFingerprint('test-machine');

    $context = new LicenseContext($key, null, $machine);

    $pipeline = app(LicenseUsageValidationPipeline::class);
    $result = $pipeline->process($context);

    expect($result)->toBeInstanceOf(LicenseContext::class)
        ->and($result->license)->not->toBeNull();
});
