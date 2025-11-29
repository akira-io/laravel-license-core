# Configuration

Complete reference for all configuration options in Laravel License Core.

## Publishing Configuration

First, publish the configuration file:

```bash
php artisan vendor:publish --tag="laravel-license-core-config"
```

This creates `config/license.php` with all default settings.

---

## Configuration Structure

The configuration file contains these main sections:

```php
return [
    'tables' => [...],               // Database table names
    'models' => [...],               // Eloquent model classes
    'abuse_detection' => [...],      // Suspicious activity detection
    'grace_period' => [...],         // Post-expiration grace period
    'license_types' => [...],        // Per-type configuration
    'domain_validation' => [...],    // Domain restriction patterns
    'key_generation' => [...],       // License key format
    'pipeline' => [...],             // Pipeline stage ordering
    'credits' => [...],              // Credit consumption rules
];
```

---

## 1. Database Tables

### Configuration

```php
'tables' => [
    'licenses' => 'licenses',
    'activations' => 'license_activations',
    'usages' => 'license_usages',
    'events' => 'license_events',
],
```

### Customize Table Names

Change the table names if you have naming conventions:

```php
'tables' => [
    'licenses' => 'app_licenses',
    'activations' => 'app_license_activations',
    'usages' => 'app_license_usages',
    'events' => 'app_license_events',
],
```

After changing table names:
1. Update migrations
2. Regenerate the migrations if needed
3. Run `php artisan migrate`

### Table Structure

| Table | Purpose |
|-------|---------|
| **licenses** | License records with type, status, expiration |
| **license_activations** | Machine/domain activations for licenses |
| **license_usages** | Credit consumption tracking |
| **license_events** | Audit log of all license events |

---

## 2. Models

### Configuration

```php
'models' => [
    'license' => \Akira\LaravelLicense\Models\License::class,
    'activation' => \Akira\LaravelLicense\Models\LicenseActivation::class,
    'usage' => \Akira\LaravelLicense\Models\LicenseUsage::class,
    'event' => \Akira\LaravelLicense\Models\LicenseEvent::class,
],
```

### Custom Models

Extend the package models for your application:

```php
// app/Models/License.php
namespace App\Models;

use Akira\LaravelLicense\Models\License as BaseModel;

class License extends BaseModel
{
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function isPremium(): bool
    {
        return $this->meta['plan'] === 'premium';
    }
}
```

Then update configuration:

```php
'models' => [
    'license' => \App\Models\License::class,
    'activation' => \Akira\LaravelLicense\Models\LicenseActivation::class,
    'usage' => \Akira\LaravelLicense\Models\LicenseUsage::class,
    'event' => \Akira\LaravelLicense\Models\LicenseEvent::class,
],
```

---

## 3. License Types

### Configuration

```php
'license_types' => [
    'lifetime' => [
        'requires_activation' => false,
        'requires_update_check' => false,
        'supports_grace_period' => false,
        'fallback_on_expiry' => false,
    ],
    'annual' => [
        'requires_activation' => true,
        'requires_update_check' => true,
        'supports_grace_period' => false,
        'fallback_on_expiry' => false,
    ],
    'subscription' => [
        'requires_activation' => true,
        'requires_update_check' => true,
        'supports_grace_period' => true,
        'fallback_on_expiry' => false,
    ],
    'trial' => [
        'requires_activation' => true,
        'requires_update_check' => true,
        'supports_grace_period' => true,
        'fallback_on_expiry' => false,
    ],
    'credits' => [
        'requires_activation' => false,
        'requires_update_check' => false,
        'supports_grace_period' => false,
        'fallback_on_expiry' => false,
    ],
],
```

### Per-Type Configuration

Each license type has 4 settings:

| Setting | Purpose | Default |
|---------|---------|---------|
| **requires_activation** | Must activate on machine | Type dependent |
| **requires_update_check** | Requires update entitlement validation | Type dependent |
| **supports_grace_period** | Can have extended validity after expiration | Type dependent |
| **fallback_on_expiry** | Continue operation after expiration | Type dependent |

### License Type Details

#### Lifetime
- Never expires
- Single activation optional
- No update checks

```php
$license = License::create([
    'type' => LicenseType::LIFETIME,
    'expires_at' => null,  // Never expires
    'max_activations' => 1,
]);
```

#### Annual
- Year-based expiration
- Requires activation
- Supports software version coverage
- No grace period by default

```php
$license = License::create([
    'type' => LicenseType::ANNUAL,
    'expires_at' => now()->addYear(),
    'max_activations' => 5,
]);
```

#### Subscription
- Month-based expiration
- Requires activation
- Supports grace period
- Renewal expected

```php
$license = License::create([
    'type' => LicenseType::SUBSCRIPTION,
    'expires_at' => now()->addMonth(),
    'grace_ends_at' => now()->addMonth()->addDays(30),
    'max_activations' => 3,
]);
```

#### Trial
- Limited trial duration
- Requires activation
- Supports grace period
- For evaluation

```php
$license = License::create([
    'type' => LicenseType::TRIAL,
    'expires_at' => now()->addDays(30),
    'grace_ends_at' => now()->addDays(37),
    'max_activations' => 1,
]);
```

#### Credits
- No expiration date
- No activation required
- Credit consumption tracked
- Usage-based billing

```php
$license = License::create([
    'type' => LicenseType::CREDITS,
    'expires_at' => null,
]);

$license->usages()->create([
    'consumed_units' => 0,
    'limit' => 10000,
]);
```

---

## 4. Grace Period

### Configuration

```php
'grace_period' => [
    'lifetime' => null,        // No grace for lifetime
    'annual' => null,          // No grace by default
    'subscription' => 30,      // 30 days after expiration
    'trial' => 7,              // 7 days after expiration
    'credits' => null,         // N/A for credits
],
```

### Grace Period Logic

After a license expires, it enters a grace period if configured:

```php
// During grace period, license is still valid
$license->isExpired();     // true
$license->inGracePeriod(); // true - validation passes

// After grace period ends, license is invalid
$license->inGracePeriod(); // false - validation fails
```

### Customizing Grace Period

Allow your customers extra time after expiration:

```php
'grace_period' => [
    'subscription' => 14,  // 2 weeks
    'trial' => 3,         // 3 days
],
```

---

## 5. Domain Validation

### Configuration

```php
'domain_validation' => [
    'pattern_type' => 'glob',        // 'glob', 'exact', or 'regex'
    'case_sensitive' => false,       // Domain matching case sensitivity
],
```

### Pattern Types

#### Glob (Recommended for Most Cases)

Simple wildcard matching with `*`:

```php
'domain_validation' => [
    'pattern_type' => 'glob',
    'case_sensitive' => false,
],

// In license meta:
'allowed_domains' => [
    'example.com',           // Exact match
    '*.example.com',         // All subdomains
    '*.prod.example.com',    // Nested subdomains
],
```

**Good for**: SaaS with subdomain-based tenants, API domains, multiple servers

#### Exact

Requires exact domain match:

```php
'domain_validation' => [
    'pattern_type' => 'exact',
    'case_sensitive' => false,
],

// In license meta:
'allowed_domains' => [
    'api.example.com',    // Only this exact domain
    'admin.example.com',  // Only this exact domain
],
```

**Good for**: Single-tenant applications, fixed domain installations

#### Regex

Full regular expression support:

```php
'domain_validation' => [
    'pattern_type' => 'regex',
    'case_sensitive' => false,
],

// In license meta:
'allowed_domains' => [
    '^(api|admin|app)\.example\.com$',    // Multiple specific domains
    '^.*\.prod\.example\.com$',            // Any production subdomain
    '^client-\d+\.example\.com$',          // Dynamic client domains
],
```

**Good for**: Complex domain rules, per-customer subdomains, environment-based domains

### Domain Validation in License

```php
// Create license with domain restrictions
$license = License::create([
    'key' => 'LIC-xxx',
    'type' => LicenseType::ANNUAL,
    'meta' => [
        'allowed_domains' => ['example.com', '*.example.com'],
        'blocked_domains' => ['spam.example.com'],  // Block even if matches allowed
    ],
]);

// Validate domain
try {
    license()->validateUsage(
        key: $license->key,
        machine: $machineHash,
        domain: 'api.example.com',
        activate: true
    );
    // Domain is allowed
} catch (DomainNotAllowedException) {
    // Domain not in allowed list
} catch (DomainBlockedException) {
    // Domain is blocked
}
```

---

## 6. Abuse Detection

### Configuration

```php
'abuse_detection' => [
    'enabled' => true,
    'window_minutes' => 10,
    'activation_threshold' => 10,
    'events_to_monitor' => ['activated'],
    'action_on_abuse' => 'log',
],
```

### Settings

| Setting | Purpose | Default |
|---------|---------|---------|
| **enabled** | Enable abuse detection | true |
| **window_minutes** | Time window for counting activations | 10 |
| **activation_threshold** | Max activations in window | 10 |
| **events_to_monitor** | Which events trigger abuse detection | ['activated'] |
| **action_on_abuse** | Action when abuse detected | 'log' |

### How It Works

Tracks rapid activation patterns to detect shared licenses:

```php
// Example: 10 activations in 10 minutes triggers abuse detection
// Events: activated on 5 different machines within 10 minutes
// Result: Abuse pattern detected

license()->validateUsage(
    key: $license->key,
    machine: $machine1,
    activate: true  // 1st activation
);

license()->validateUsage(
    key: $license->key,
    machine: $machine2,
    activate: true  // 2nd activation
);

// ... 8 more different machines in same window ...

// 10th activation triggers abuse detection
```

### Customizing Abuse Detection

```php
'abuse_detection' => [
    'enabled' => true,
    'window_minutes' => 5,           // Shorter window = stricter
    'activation_threshold' => 3,     // Lower threshold = stricter
    'events_to_monitor' => [
        'activated',
        'rotated',  // Also monitor license rotations
    ],
    'action_on_abuse' => 'log',      // Options: 'log', 'suspend'
],
```

### Actions

- **'log'** (default) - Log the abuse attempt (no action taken)
- **'suspend'** - Suspend the license

---

## 7. Key Generation

### Configuration

```php
'key_generation' => [
    'prefix' => 'LIC',    // Prefix for generated keys
    'format' => 'uuid',   // 'uuid' or 'sequential'
],
```

### Default Format

Generated keys look like:

```
LIC-550e8400-e29b-41d4-a716-446655440000
```

- **Prefix**: LIC
- **Format**: UUID v4

### Customizing Key Format

```php
'key_generation' => [
    'prefix' => 'PRO',  // Custom prefix for pro licenses
    'format' => 'uuid',
],
// Results in: PRO-{uuid}
```

### Key Generation in Code

```php
use Akira\LaravelLicense\Support\KeyGenerator;

$generator = app(KeyGenerator::class);
$key = $generator->generate();  // LIC-{uuid}
```

---

## 8. Pipeline

### Configuration

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

### Usage Pipeline Stages

Order matters! Each stage depends on previous ones:

1. **resolve_license** - Find license by key
2. **status_check** - Verify not revoked/suspended
3. **expiration_usage** - Check expiration
4. **grace_period** - Apply grace period logic
5. **domain_check** - Validate domain restrictions
6. **machine_check** - Check activation limits
7. **credits_usage** - Validate credit availability
8. **abuse_heuristics** - Detect abuse patterns

### Update Pipeline Stages

1. **resolve_license** - Find license
2. **status_check** - Verify validity
3. **expiration_usage** - Check expiration
4. **grace_period** - Apply grace period
5. **update_window** - Check version coverage

### Customizing Pipeline

Skip or reorder stages:

```php
'pipeline' => [
    'usage' => [
        'resolve_license',
        'status_check',
        'expiration_usage',
        // Skip domain_check
        'machine_check',
        'credits_usage',
        // Skip abuse_heuristics
    ],
],
```

### Custom Stages

Add custom validation stages:

```php
'pipeline' => [
    'usage' => [
        'resolve_license',
        'status_check',
        'expiration_usage',
        'grace_period',
        'my_custom_stage',  // Your custom stage
        'domain_check',
        'machine_check',
        'credits_usage',
        'abuse_heuristics',
    ],
],
```

See [Custom Stages Guide](04-pipelines.md#custom-stages) for implementation.

---

## 9. Credits

### Configuration

```php
'credits' => [
    'allow_partial_consumption' => false,
    'allow_refund' => false,
],
```

### Settings

| Setting | Purpose | Default |
|---------|---------|---------|
| **allow_partial_consumption** | Allow consuming partial credits | false |
| **allow_refund** | Allow refunding consumed credits | false |

### Partial Consumption

If disabled (default), consumption fails if insufficient credits:

```php
'allow_partial_consumption' => false,

// Consumption fails if less than requested amount available
license()->consumeCredits(
    key: $license->key,
    amount: 1000  // Fails if less than 1000 available
);
```

If enabled, consumes whatever is available:

```php
'allow_partial_consumption' => true,

// Consumes up to 1000, succeeds even if less available
license()->consumeCredits(
    key: $license->key,
    amount: 1000
);
```

### Refunds

Allow refunding previously consumed credits:

```php
'allow_refund' => true,

// Refund 100 credits to user's license
$license->usages()->first()->update([
    'consumed_units' => DB::raw('consumed_units - 100'),
]);
```

---

## Accessing Configuration

### Using ConfigManager

Recommended approach using the singleton `ConfigManager`:

```php
use Akira\LaravelLicense\Support\ConfigManager;

$config = app(ConfigManager::class);

// Get table names
$table = $config->getLicenseTable();  // 'licenses'

// Get model classes
$modelClass = $config->getModelClass('license');

// Get configuration objects
$domainConfig = $config->getDomainValidation();  // DomainValidationConfiguration
$graceConfig = $config->getGracePeriod();        // GracePeriodConfiguration

// Get raw config
$value = $config->get('abuse_detection.enabled');
```

### Using Laravel's Config Helper

Direct configuration access:

```php
// Get table name
$table = config('license.tables.licenses');

// Get model class
$modelClass = config('license.models.license');

// Get setting
$enabled = config('license.abuse_detection.enabled');
```

### Environment Variables

Use environment variables for environment-specific settings:

```php
// config/license.php
return [
    'abuse_detection' => [
        'enabled' => env('LICENSE_ABUSE_DETECTION', true),
        'action_on_abuse' => env('LICENSE_ABUSE_ACTION', 'log'),
    ],
];
```

In `.env`:

```env
LICENSE_ABUSE_DETECTION=true
LICENSE_ABUSE_ACTION=suspend
```

---

## Complete Default Configuration

Here's the complete `config/license.php`:

```php
<?php

return [
    'tables' => [
        'licenses' => 'licenses',
        'activations' => 'license_activations',
        'usages' => 'license_usages',
        'events' => 'license_events',
    ],

    'models' => [
        'license' => \Akira\LaravelLicense\Models\License::class,
        'activation' => \Akira\LaravelLicense\Models\LicenseActivation::class,
        'usage' => \Akira\LaravelLicense\Models\LicenseUsage::class,
        'event' => \Akira\LaravelLicense\Models\LicenseEvent::class,
    ],

    'abuse_detection' => [
        'enabled' => true,
        'window_minutes' => 10,
        'activation_threshold' => 10,
        'events_to_monitor' => ['activated'],
        'action_on_abuse' => 'log',
    ],

    'grace_period' => [
        'lifetime' => null,
        'annual' => null,
        'subscription' => 30,
        'trial' => 7,
        'credits' => null,
    ],

    'license_types' => [
        'lifetime' => [
            'requires_activation' => false,
            'requires_update_check' => false,
            'supports_grace_period' => false,
            'fallback_on_expiry' => false,
        ],
        'annual' => [
            'requires_activation' => true,
            'requires_update_check' => true,
            'supports_grace_period' => false,
            'fallback_on_expiry' => false,
        ],
        'subscription' => [
            'requires_activation' => true,
            'requires_update_check' => true,
            'supports_grace_period' => true,
            'fallback_on_expiry' => false,
        ],
        'trial' => [
            'requires_activation' => true,
            'requires_update_check' => true,
            'supports_grace_period' => true,
            'fallback_on_expiry' => false,
        ],
        'credits' => [
            'requires_activation' => false,
            'requires_update_check' => false,
            'supports_grace_period' => false,
            'fallback_on_expiry' => false,
        ],
    ],

    'domain_validation' => [
        'pattern_type' => 'glob',
        'case_sensitive' => false,
    ],

    'key_generation' => [
        'prefix' => 'LIC',
        'format' => 'uuid',
    ],

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

    'credits' => [
        'allow_partial_consumption' => false,
        'allow_refund' => false,
    ],
];
```

---

## Configuration Best Practices

### 1. Use Environment Variables for Sensitive Settings

```php
'abuse_detection' => [
    'action_on_abuse' => env('LICENSE_ABUSE_ACTION', 'log'),
],
```

### 2. Start Conservative, Adjust Based on Telemetry

```php
// Start strict, loosen if too restrictive
'abuse_detection' => [
    'activation_threshold' => 5,  // Start low
],
```

### 3. Document Custom Configuration

```php
return [
    // Custom: Maximum activations per license
    // Defaults to 5, override in config
    'max_activations' => env('LICENSE_MAX_ACTIVATIONS', 5),
];
```

### 4. Cache Configuration in Production

```bash
php artisan config:cache
```

Clear when updating:

```bash
php artisan config:clear
```

---

## Troubleshooting

### Configuration Not Taking Effect

Clear the configuration cache:

```bash
php artisan config:clear
php artisan cache:clear
```

### Pipeline Stage Not Running

Check pipeline order and that stage class exists:

```php
'pipeline' => [
    'usage' => [
        'resolve_license',
        'my_custom_stage',  // Make sure this exists
    ],
],
```

### Domain Validation Not Working

Verify pattern type matches your domains:

```php
// If using glob:
'domain_validation' => [
    'pattern_type' => 'glob',
],
// Patterns like '*.example.com' will work

// If using regex:
'domain_validation' => [
    'pattern_type' => 'regex',
],
// Patterns like '^.*\.example\.com$' will work
```

---

**Previous**: [Installation](01-installation.md) | **Next**: [Models](03-models.md)
