<?php

declare(strict_types=1);

use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Pipelines\LicenseUpdateValidationPipeline;
use Akira\LaravelLicense\ValueObjects\LicenseContext;
use Akira\LaravelLicense\ValueObjects\LicenseKey;

test('pipeline processes context through all stages', function () {
    $expiryDate = now()->addYear();
    $license = License::factory()->create([
        'status' => 'active',
        'type' => LicenseType::SUBSCRIPTION->value,
        'expires_at' => $expiryDate,
    ]);

    $key = LicenseKey::fromString($license->key);
    $releaseDate = now()->subMonth();

    $context = new LicenseContext($key, null, null, null, $releaseDate);

    $pipeline = app(LicenseUpdateValidationPipeline::class);
    $result = $pipeline->process($context);

    expect($result)->toBeInstanceOf(LicenseContext::class)
        ->and($result->license)->not->toBeNull();
});
