# Laravel License Core Documentation

Complete documentation for Laravel License Core - a flexible, type-safe license management system for Laravel
applications.

## Quick Navigation

- **[Getting Started](#getting-started)** - Installation and setup
- **[Core Concepts](#core-concepts)** - Understanding the system
- **[Configuration](#configuration)** - All available options
- **[Usage Examples](#usage-examples)** - Practical code examples
- **[Advanced Topics](#advanced-topics)** - Pipelines, custom stages, etc.

---

## Getting Started

### Installation & Setup

1. [Installation](01-installation.md) - Install via Composer
2. [Configuration](02-configuration.md) - Configure tables, models, and behavior

### First Steps

- Publish configuration: `php artisan vendor:publish --tag="laravel-license-core-config"`
- Run migrations: `php artisan migrate`
- Read [Quick Start](#quick-start-example) below

---

## Core Concepts

### 1. License Types

Laravel License Core supports 5 license types:

| Type             | Activation | Expiration | Updates        | Grace Period | Use Case                 |
|------------------|------------|------------|----------------|--------------|--------------------------|
| **Lifetime**     | Optional   | Never      | Optional       | No           | Permanent licenses       |
| **Annual**       | Required   | Yearly     | Supported      | Optional     | Year-based subscriptions |
| **Subscription** | Required   | Monthly    | Supported      | Configurable | Subscription services    |
| **Trial**        | Required   | Limited    | Supported      | Configurable | Trial versions           |
| **Credits**      | Optional   | By usage   | Not applicable | No           | Credit-based access      |

Learn more: [Models & Data](03-models.md)

### 2. License Validation Pipeline

Every license validation goes through a **pipeline of stages**:

**For Usage Validation** (8 stages):

1. **ResolveLicenseStage** - Find the license by key
2. **StatusCheckStage** - Verify license isn't revoked or suspended
3. **ExpirationUsageStage** - Check if license is expired
4. **GracePeriodStage** - Apply grace period logic if applicable
5. **DomainCheckStage** - Validate domain restrictions
6. **MachineCheckStage** - Check machine activation limits
7. **CreditsUsageStage** - Validate sufficient credits available
8. **AbuseHeuristicsStage** - Detect suspicious patterns

**For Update Validation** (5 stages):

1. **ResolveLicenseStage** - Find the license
2. **StatusCheckStage** - Verify license is valid
3. **ExpirationUsageStage** - Check expiration
4. **GracePeriodStage** - Apply grace period
5. **UpdateWindowStage** - Check if version is covered

Learn more: [Pipeline Architecture](04-pipelines.md)

### 3. License Structure

Every license has:

- **Key** - Unique identifier (e.g., "LIC-550e8400-e29b-41d4-a716-446655440000")
- **Type** - One of: lifetime, annual, subscription, trial, credits
- **Status** - active, expired, suspended, or revoked
- **Metadata** - Encrypted custom data (customer info, plan name, etc.)
- **Scopes** - Permissions/features the license grants
- **Expiration** - Optional expiration date (except lifetime and credits)
- **Grace Period** - Extended validity after expiration (subscription/trial only)

Learn more: [Models & Data](03-models.md)

### 4. License Validation

Two main validation types:

```php
// Usage validation: "Can this installation use the software?"
license()->validateUsage(
    key: 'LIC-xxx',
    machine: 'sha256-hash',
    domain: 'api.example.com',
    activate: true  // Create activation record
);

// Update validation: "Can this installation access this version?"
license()->validateUpdate(
    key: 'LIC-xxx',
    releaseDate: now(),
    domain: 'api.example.com',
    machine: 'sha256-hash'
);
```

Learn more: [Usage Guide](05-usage-guide.md)

---

## Configuration

### Configuration File

All settings are in `config/license.php`:

```php
return [
    'tables' => [...],           // Database table names
    'models' => [...],           // Eloquent model classes
    'abuse_detection' => [...],  // Suspicious activity detection
    'grace_period' => [...],     // Post-expiration grace period
    'license_types' => [...],    // Per-type configuration
    'domain_validation' => [...],// Domain restriction patterns
    'key_generation' => [...],   // License key format
    'pipeline' => [...],         // Pipeline stage ordering
    'credits' => [...],          // Credit consumption rules
];
```

### Key Configuration Areas

1. **[Database Tables](02-configuration.md#tables)** - Customize table names
2. **[Models](02-configuration.md#models)** - Use custom Eloquent models
3. **[License Types](02-configuration.md#license-types)** - Per-type behavior
4. **[Domain Validation](02-configuration.md#domain-validation)** - Allow/block domains with glob/regex/exact patterns
5. **[Grace Period](02-configuration.md#grace-period)** - Extended validity after expiration
6. **[Abuse Detection](02-configuration.md#abuse-detection)** - Detect suspicious activation patterns
7. **[Credits](02-configuration.md#credits)** - Credit consumption rules

Full reference: [Configuration Guide](02-configuration.md)

---

## Usage Examples

### Quick Start Example

```php
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Enums\LicenseStatus;

// 1. Create a license
$license = License::create([
    'key' => 'LIC-' . Str::uuid(),
    'type' => LicenseType::ANNUAL,
    'status' => LicenseStatus::ACTIVE,
    'max_activations' => 5,
    'expires_at' => now()->addYear(),
    'meta' => [
        'customer_email' => 'john@example.com',
        'plan' => 'professional',
    ],
]);

// 2. Validate license usage
try {
    license()->validateUsage(
        key: $license->key,
        machine: hash('sha256', gethostname()),
        domain: request()->getHost(),
        activate: true // Create activation record
    );
    // License is valid!
} catch (\Akira\LaravelLicense\Exceptions\LicenseException $e) {
    // Handle error
}

// 3. Track usage (for credit-based licenses)
$license->usages()->create([
    'consumed_units' => 0,
    'limit' => 10000,
]);

// 4. Consume credits
license()->consumeCredits(
    key: $license->key,
    amount: 100
);
```

### More Examples

- [Creating Different License Types](05-usage-guide.md#creating-licenses)
- [Domain Restrictions](05-usage-guide.md#domain-validation)
- [Activation & Deactivation](05-usage-guide.md#managing-activations)
- [Credit Consumption](05-usage-guide.md#tracking-usage)
- [Event Logging](05-usage-guide.md#event-logging)
- [Middleware Implementation](05-usage-guide.md#middleware)

---

## Advanced Topics

### Custom Configuration

- [Customizing License Types](02-configuration.md#license-types)
- [Custom Pipeline Stages](04-pipelines.md#custom-stages)
- [Abuse Detection Rules](02-configuration.md#abuse-detection)
- [Domain Validation Patterns](02-configuration.md#domain-validation)

### Value Objects

Laravel License Core uses immutable value objects for type safety:

- **LicenseKey** - Wrapper for license key string
- **DomainName** - Extract and validate domain names
- **MachineFingerprint** - Machine identifier hash
- **LicenseContext** - Request context and state
- **LicenseScopes** - Permission/feature grants
- **UsageAmount** - Track usage limits and consumption

Learn more: [Value Objects](06-value-objects.md)

### Actions & Business Logic

Business logic is implemented as actions:

- **ActivateLicenseAction** - Create machine activation
- **ConsumeCreditsAction** - Deduct credits from pool
- **RotateLicenseKeyAction** - Generate new key
- **ValidateUsageAction** - Validate for usage
- **ValidateUpdateAction** - Validate for updates

Learn more: [Actions Guide](07-actions.md)

### Exception Handling

All license errors extend `LicenseException`:

- `DomainBlockedException` - Domain is blocked
- `DomainNotAllowedException` - Domain not in allow list
- `LicenseExpiredException` - License expired
- `LicenseNotFoundException` - License key not found
- `LicenseRevokedException` - License revoked
- `LicenseSuspendedException` - License suspended
- `InsufficientCreditsException` - Not enough credits
- And 4 more...

Learn more: [Exception Reference](08-exceptions.md)

---

## Documentation Map

| Document                                                     | Purpose                                  |
|--------------------------------------------------------------|------------------------------------------|
| **[00-index.md](00-index.md)**                               | This document - overview and navigation  |
| **[01-installation.md](01-installation.md)**                 | Install and publish configuration        |
| **[02-configuration.md](02-configuration.md)**               | All configuration options explained      |
| **[03-models.md](03-models.md)**                             | License, Activation, Usage, Event models |
| **[04-pipelines.md](04-pipelines.md)**                       | Pipeline architecture and custom stages  |
| **[05-usage-guide.md](05-usage-guide.md)**                   | Practical examples and common patterns   |
| **[06-value-objects.md](06-value-objects.md)**               | Immutable value objects reference        |
| **[07-actions.md](07-actions.md)**                           | Business logic actions                   |
| **[08-exceptions.md](08-exceptions.md)**                     | Exception reference and handling         |
| **[09-testing.md](09-testing.md)**                           | Testing with factories                   |
| **[10-internationalization.md](10-internationalization.md)** | Multi-language support                   |

---

## Key Classes

### Main Service

- `LaravelLicense` - Main facade for license operations

### Models

- `License` - License records
- `LicenseActivation` - Machine activations
- `LicenseUsage` - Credit usage tracking
- `LicenseEvent` - Audit log

### Configuration

- `ConfigManager` - Access and convert configurations
- `KeyGenerator` - Generate license keys

### Pipelines

- `LicenseUsageValidationPipeline` - Validate for usage
- `LicenseUpdateValidationPipeline` - Validate for updates

### Enums

- `LicenseType` - lifetime, annual, subscription, trial, credits
- `LicenseStatus` - active, expired, suspended, revoked
- `LicenseEventType` - All event types

---

## Common Tasks

### I want to...

- **Create a license** → [Creating Licenses](05-usage-guide.md#creating-licenses)
- **Validate a license** → [Validation](05-usage-guide.md#license-validation)
- **Activate a device** → [Activations](05-usage-guide.md#managing-activations)
- **Track usage/credits** → [Usage Tracking](05-usage-guide.md#tracking-usage)
- **Restrict by domain** → [Domain Validation](05-usage-guide.md#domain-validation)
- **Log license events** → [Event Logging](05-usage-guide.md#event-logging)
- **Configure license types** → [License Type Config](02-configuration.md#license-types)
- **Create custom stages** → [Custom Stages](04-pipelines.md#custom-stages)
- **Handle errors** → [Exception Handling](08-exceptions.md)
- **Write tests** → [Testing](09-testing.md)

---

## Key Features

✓ **5 License Types** - Lifetime, Annual, Subscription, Trial, Credits
✓ **Device Activation** - Limit installations with machine fingerprints
✓ **Domain Restriction** - Allow/block specific domains (glob/exact/regex)
✓ **Grace Period** - Extended validity after expiration
✓ **Credit-Based** - Pool-based consumption for flexible models
✓ **Update Entitlement** - Control which versions are covered
✓ **Abuse Detection** - Monitor suspicious activation patterns
✓ **Event Auditing** - Complete audit trail of all license events
✓ **Encrypted Metadata** - Secure storage of sensitive license data
✓ **Type-Safe** - PHP 8.4 enums and value objects

---

## Package Info

- **Version**: 1.0.0
- **License**: MIT
- **PHP Version**: 8.4+
- **Laravel Version**: 12.x+
- **Test Coverage**: 100%
- **Status**: Production Ready

---

## Getting Help

- 📖 Check the relevant documentation page
- 🔍 Search for your specific use case in examples
- ⚙️ Review [Configuration Guide](02-configuration.md) for options
- 🛠️ See [Troubleshooting](#troubleshooting) below

### Troubleshooting

**License not validating?**

- Check [License Validation Pipeline](04-pipelines.md)
- Verify [configuration](02-configuration.md)
- Review [exception handling](08-exceptions.md)

**Domain validation not working?**

- Check [Domain Validation Config](02-configuration.md#domain-validation)
- Verify pattern type (glob/exact/regex)
- See [Domain Validation Examples](05-usage-guide.md#domain-validation)

**Performance issues?**

- Enable [abuse detection](02-configuration.md#abuse-detection)
- Review [pipeline configuration](02-configuration.md#pipeline)
- Check database indexes

---

## Contributing to Documentation

Found an error? Want to add a section?

1. Check the [DOCUMENTATION_GUIDE.md](../DOCUMENTATION_GUIDE.md) for structure
2. Make your changes
3. Ensure all code examples are accurate
4. Test any provided code

---

**Last Updated**: 2025-11-29
**Status**: Complete Rewrite
