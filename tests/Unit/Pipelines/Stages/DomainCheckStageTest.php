<?php

declare(strict_types=1);

use Akira\LaravelLicense\Exceptions\DomainBlockedException;
use Akira\LaravelLicense\Exceptions\DomainNotAllowedException;
use Akira\LaravelLicense\Exceptions\LicenseNotLoadedException;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Pipelines\Stages\DomainCheckStage;
use Akira\LaravelLicense\ValueObjects\DomainName;
use Akira\LaravelLicense\ValueObjects\LicenseContext;
use Akira\LaravelLicense\ValueObjects\LicenseKey;

it('passes when no domain is provided', function () {
    $license = License::factory()->create();
    $key = LicenseKey::fromString($license->key);
    $context = new LicenseContext(key: $key, domain: null, license: $license);

    $stage = new DomainCheckStage();
    $result = $stage($context);

    expect($result)->toBe($context);
});

it('passes when no domain restrictions are set', function () {
    $license = License::factory()->create([
        'meta' => [],
    ]);

    $key = LicenseKey::fromString($license->key);
    $domain = DomainName::fromUrlOrHost('example.com');
    $context = new LicenseContext(key: $key, domain: $domain, license: $license);

    $stage = new DomainCheckStage();
    $result = $stage($context);

    expect($result)->toBe($context);
});

it('passes when domain is in allowed list', function () {
    $license = License::factory()->create([
        'meta' => [
            'allowed_domains' => ['example.com', 'test.com'],
        ],
    ]);

    $key = LicenseKey::fromString($license->key);
    $domain = DomainName::fromUrlOrHost('example.com');
    $context = new LicenseContext(key: $key, domain: $domain, license: $license);

    $stage = new DomainCheckStage();
    $result = $stage($context);

    expect($result)->toBe($context);
});

it('passes when domain matches wildcard pattern in allowed list', function () {
    $license = License::factory()->create([
        'meta' => [
            'allowed_domains' => ['*.example.com'],
        ],
    ]);

    $key = LicenseKey::fromString($license->key);
    $domain = DomainName::fromUrlOrHost('subdomain.example.com');
    $context = new LicenseContext(key: $key, domain: $domain, license: $license);

    $stage = new DomainCheckStage();
    $result = $stage($context);

    expect($result)->toBe($context);
});

it('throws exception when domain is blocked', function () {
    $license = License::factory()->create([
        'meta' => [
            'blocked_domains' => ['blocked.com'],
        ],
    ]);

    $key = LicenseKey::fromString($license->key);
    $domain = DomainName::fromUrlOrHost('blocked.com');
    $context = new LicenseContext(key: $key, domain: $domain, license: $license);

    $stage = new DomainCheckStage();
    $stage($context);
})->throws(DomainBlockedException::class);

it('throws exception when domain matches blocked wildcard pattern', function () {
    $license = License::factory()->create([
        'meta' => [
            'blocked_domains' => ['*.blocked.com'],
        ],
    ]);

    $key = LicenseKey::fromString($license->key);
    $domain = DomainName::fromUrlOrHost('subdomain.blocked.com');
    $context = new LicenseContext(key: $key, domain: $domain, license: $license);

    $stage = new DomainCheckStage();
    $stage($context);
})->throws(DomainBlockedException::class);

it('throws exception when domain is not in allowed list', function () {
    $license = License::factory()->create([
        'meta' => [
            'allowed_domains' => ['example.com'],
        ],
    ]);

    $key = LicenseKey::fromString($license->key);
    $domain = DomainName::fromUrlOrHost('notallowed.com');
    $context = new LicenseContext(key: $key, domain: $domain, license: $license);

    $stage = new DomainCheckStage();
    $stage($context);
})->throws(DomainNotAllowedException::class);

it('blocks domain even if it is in allowed list', function () {
    $license = License::factory()->create([
        'meta' => [
            'allowed_domains' => ['example.com'],
            'blocked_domains' => ['example.com'],
        ],
    ]);

    $key = LicenseKey::fromString($license->key);
    $domain = DomainName::fromUrlOrHost('example.com');
    $context = new LicenseContext(key: $key, domain: $domain, license: $license);

    $stage = new DomainCheckStage();
    $stage($context);
})->throws(DomainBlockedException::class);

it('throws exception when license is not loaded', function () {
    $key = LicenseKey::fromString('XXXX-XXXX-XXXX-XXXX');
    $domain = DomainName::fromUrlOrHost('example.com');
    $context = new LicenseContext(key: $key, domain: $domain);

    $stage = new DomainCheckStage();
    $stage($context);
})->throws(LicenseNotLoadedException::class);

it('handles complex wildcard patterns', function () {
    $license = License::factory()->create([
        'meta' => [
            'allowed_domains' => ['*.*.example.com'],
        ],
    ]);

    $key = LicenseKey::fromString($license->key);
    $domain = DomainName::fromUrlOrHost('sub.test.example.com');
    $context = new LicenseContext(key: $key, domain: $domain, license: $license);

    $stage = new DomainCheckStage();
    $result = $stage($context);

    expect($result)->toBe($context);
});

it('matches domain patterns case-insensitively', function () {
    $license = License::factory()->create([
        'meta' => [
            'allowed_domains' => ['EXAMPLE.COM'],
        ],
    ]);

    $key = LicenseKey::fromString($license->key);
    $domain = DomainName::fromUrlOrHost('example.com');
    $context = new LicenseContext(key: $key, domain: $domain, license: $license);

    $stage = new DomainCheckStage();
    $result = $stage($context);

    expect($result)->toBe($context);
});
