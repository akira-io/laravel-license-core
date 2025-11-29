# Laravel License

[![Latest Version on Packagist](https://img.shields.io/packagist/v/akira/laravel-license-core.svg?style=flat-square)](https://packagist.org/packages/akira/laravel-license-core)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/akira/laravel-license-core/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/akira/laravel-license-core/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/akira/laravel-license-core/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/akira/laravel-license-core/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/akira/laravel-license-core.svg?style=flat-square)](https://packagist.org/packages/akira/laravel-license-core)

A modern, secure and extensible licensing engine for Laravel applications. This package provides the complete domain logic, models, and business rules required to implement a full-featured licensing system inside Laravel applications.

## Requirements

- PHP 8.4 or higher
- Laravel 12.x or higher

## Features

- Multiple license types: Lifetime, Annual, Subscription, Trial, and Credits-based
- License status management: Active, Expired, Suspended, and Revoked
- Activation tracking with domain, machine hash, IP, and user agent
- Usage monitoring with consumed units and limits
- Event logging for complete audit trail
- Grace period support for expired licenses
- Encrypted metadata storage
- Comprehensive configuration system for all business rules
- Configurable table names and models
- Dynamic pipeline stage loading
- Custom key generation formats (UUID and sequential)
- Full factory support for testing
- 100% test coverage (438 tests, 764 assertions)

## Installation

You can install the package via composer:

```bash
composer require akira/laravel-license-core
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-license-core-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-license-core-config"
```

This is the contents of the published config file:

```php
return [
    'tables' => [
        'licenses' => 'licenses',
        'activations' => 'license_activations',
        'usages' => 'license_usages',
        'events' => 'license_events',
    ],

    'models' => [
        'license' => License::class,
        'activation' => LicenseActivation::class,
        'usage' => LicenseUsage::class,
        'event' => LicenseEvent::class,
    ],
];
```

## Usage

### Using the License Facade

The simplest way to work with licenses is through the `License` facade:

```php
use Akira\LaravelLicense\Facades\License;
use Akira\LaravelLicense\ValueObjects\LicenseData;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Enums\LicenseStatus;

// Create a license with auto-generated key
$license = License::createWithAutoKey(
    new LicenseData(
        key: '',
        type: LicenseType::ANNUAL,
        status: LicenseStatus::ACTIVE,
        maxActivations: 5,
        maxSeats: 10,
        fallback: false,
        scopes: ['feature:premium'],
        meta: ['customer_email' => 'john@example.com'],
        expiresAt: now()->addYear(),
        graceEndsAt: null,
    )
);

// Validate license usage
$isValid = License::validateUsage(
    key: $license->key,
    machine: 'unique-machine-fingerprint',
    domain: 'example.com',
    activate: true
);

// Validate license for updates
$canUpdate = License::validateUpdate(
    key: $license->key,
    releaseDate: now(),
    domain: 'example.com',
    machine: 'unique-machine-fingerprint'
);

// Consume credits
$success = License::consumeCredits(
    key: $license->key,
    amount: 10
);

// Rotate license key
$newKey = License::rotateKey($license->key);
```

### Creating a License Directly

You can also create licenses using the model:

```php
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Enums\LicenseStatus;

$license = License::create([
    'key' => 'XXXX-XXXX-XXXX-XXXX',
    'type' => LicenseType::ANNUAL->value,
    'status' => LicenseStatus::ACTIVE->value,
    'max_activations' => 5,
    'max_seats' => 10,
    'expires_at' => now()->addYear(),
]);
```

### Using Factories

```php
// Create a basic license
$license = License::factory()->create();

// Create an active annual license
$license = License::factory()
    ->active()
    ->annual()
    ->create();

// Create a license with grace period
$license = License::factory()
    ->withGracePeriod()
    ->create();

// Create a license with metadata
$license = License::factory()
    ->withMeta(['customer_id' => 123])
    ->create();
```

### License Activation

```php
use Akira\LaravelLicense\Models\LicenseActivation;

$activation = LicenseActivation::create([
    'license_id' => $license->id,
    'domain' => 'example.com',
    'machine_hash' => hash('sha256', 'unique-machine-id'),
    'ip' => request()->ip(),
    'user_agent' => request()->userAgent(),
]);

// Using factory
$activation = LicenseActivation::factory()
    ->forLicense($license)
    ->withDomain('example.com')
    ->create();
```

### Usage Tracking

```php
use Akira\LaravelLicense\Models\LicenseUsage;

$usage = LicenseUsage::create([
    'license_id' => $license->id,
    'consumed_units' => 0,
    'limit' => 1000,
]);

// Check remaining units
$remaining = $usage->remaining(); // 1000

// Consume units
$usage->update(['consumed_units' => 250]);
$remaining = $usage->remaining(); // 750

// Using factory
$usage = LicenseUsage::factory()
    ->forLicense($license)
    ->withLimit(1000)
    ->fresh() // 0 consumed units
    ->create();
```

### Event Logging

```php
use Akira\LaravelLicense\Models\LicenseEvent;
use Akira\LaravelLicense\Enums\LicenseEventType;

$event = LicenseEvent::create([
    'license_id' => $license->id,
    'type' => LicenseEventType::ACTIVATED->value,
    'payload' => [
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ],
    'created_at' => now(),
]);

// Using factory
$event = LicenseEvent::factory()
    ->forLicense($license)
    ->activated()
    ->withPayload(['user_id' => 123])
    ->create();
```

### Working with License Status

```php
// Check if license is expired
if ($license->isExpired()) {
    // Handle expired license
}

// Check if license is in grace period
if ($license->inGracePeriod()) {
    // Show warning to user
}

// Get license status as enum
$status = $license->statusEnum(); // LicenseStatus enum

// Get license type as enum
$type = $license->typeEnum(); // LicenseType enum
```

### Relationships

```php
// Get all activations for a license
$activations = $license->activations;

// Get all usage records for a license
$usages = $license->usages;

// Get all events for a license
$events = $license->events;

// Get license from activation
$license = $activation->license;
```

### Configuration System

The package includes a comprehensive configuration system that controls all business rules without code changes. Configuration is centralized in `config/license.php`.

#### Key Configuration Features

**Key Generation**:
```php
'key_generation' => [
    'prefix' => 'LIC',           // Key prefix
    'format' => 'uuid',          // 'uuid' or 'sequential'
],
```

**Grace Period**:
```php
'grace_period' => [
    'lifetime' => null,          // Days after lifetime expiration
    'annual' => null,            // Days after annual expiration
    'subscription' => 30,        // Days after subscription expiration
    'trial' => 7,                // Days after trial expiration
    'credits' => null,           // Days after credits expiration
],
```

**Abuse Detection**:
```php
'abuse_detection' => [
    'enabled' => true,
    'window_minutes' => 10,
    'activation_threshold' => 10,
    'events_to_monitor' => ['activated'],
    'action_on_abuse' => 'log',  // 'log' or 'suspend'
],
```

**Pipeline Configuration**:
```php
'pipeline' => [
    'usage' => [
        'resolve_license',
        'status_check',
        'expiration_usage',
        'grace_period',
        'domain_check',
        'machine_check',
        'credits_usage',
        'abuse_heuristics',
    ],
    'update' => [
        'resolve_license',
        'status_check',
        'expiration_usage',
        'grace_period',
        'update_window',
    ],
],
```

**Credits Configuration**:
```php
'credits' => [
    'allow_partial_consumption' => false,
    'allow_refund' => false,
],
```

See the [Configuration Documentation](docs/02-configuration.md) for complete details on all configuration options.

### Custom Table Names

You can customize table names in the config file:

```php
'tables' => [
    'licenses' => 'my_licenses',
    'activations' => 'my_activations',
    'usages' => 'my_usages',
    'events' => 'my_events',
],
```

### Encrypted Metadata

License metadata is automatically encrypted:

```php
$license = License::create([
    'key' => 'XXXX-XXXX-XXXX-XXXX',
    'type' => LicenseType::ANNUAL->value,
    'status' => LicenseStatus::ACTIVE->value,
    'meta' => [
        'customer_email' => 'user@example.com',
        'plan_name' => 'Professional',
        'features' => ['api_access', 'priority_support'],
    ],
]);

// Metadata is automatically encrypted in database
// and decrypted when accessed
$email = $license->meta['customer_email'];
```

## Available License Types

The package includes the following license types through the `LicenseType` enum:

- `LIFETIME` - Permanent license with no expiration
- `ANNUAL` - Annual subscription that expires after one year
- `SUBSCRIPTION` - Recurring subscription-based license
- `TRIAL` - Trial license with limited time period
- `CREDITS` - Credit-based license for usage tracking

## Available License Statuses

License statuses are managed through the `LicenseStatus` enum:

- `ACTIVE` - License is active and can be used
- `EXPIRED` - License has passed its expiration date
- `SUSPENDED` - License is temporarily suspended
- `REVOKED` - License has been permanently revoked

## Available Event Types

Events are tracked using the `LicenseEventType` enum:

- `CREATED` - License was created
- `ACTIVATED` - License was activated on a device/domain
- `DEACTIVATED` - License was deactivated
- `ROTATED` - License key was rotated
- `REVOKED` - License was revoked
- `USAGE_CONSUMED` - Usage units were consumed
- `EXPIRED` - License expired
- `ABUSE_DETECTED` - Potential abuse was detected

## Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test:coverage
```

The package includes **438 tests** with **100% code coverage** across all components:

### Test Coverage

- **Commands**: LaravelLicenseCommand
- **Enums**: LicenseType, LicenseStatus, LicenseEventType
- **Facades**: License
- **Models**: License, LicenseActivation, LicenseEvent, LicenseUsage
- **Support**: ConfigManager, KeyGenerator
- **Value Objects**: DomainName, LicenseContext, LicenseKey, LicenseMeta, LicenseScopes, MachineFingerprint, UpdateEntitlement, UsageAmount
- **Configuration Objects**: AbuseDetectionConfiguration, CreditsConfiguration, DomainValidationConfiguration, GracePeriodConfiguration, KeyGenerationConfiguration, LicenseTypeConfiguration, PipelineConfiguration
- **Pipeline Stages**: All 9 validation stages with complete coverage

### Additional Test Commands

```bash
# Run specific test file
./vendor/bin/pest tests/Models/LicenseTest.php

# Run tests in specific directory
./vendor/bin/pest tests/ValueObjects/

# Run with detailed coverage
./vendor/bin/pest --coverage --min=100
```

## Documentation

Complete documentation is available in the [docs](docs/) directory:

- [Index](docs/00-index.md) - Documentation index and navigation
- [Installation](docs/01-installation.md) - Installation and setup guide
- [Configuration](docs/02-configuration.md) - Comprehensive configuration reference with all options
- [Models](docs/03-models.md) - Model documentation and relationships
- [Pipelines](docs/04-pipelines.md) - Pipeline architecture and custom stages
- [Usage Guide](docs/05-usage-guide.md) - Comprehensive usage examples and patterns
- [Value Objects](docs/06-value-objects.md) - Immutable domain objects
- [Actions](docs/07-actions.md) - Domain actions and business logic
- [Exceptions](docs/08-exceptions.md) - Exception handling and error cases
- [Testing](docs/09-testing.md) - Comprehensive testing guide and examples
- [Internationalization](docs/10-internationalization.md) - i18n and localization

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [kidiatoliny](https://github.com/kidiatoliny)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
