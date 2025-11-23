# Enums

Laravel License uses PHP 8.4+ enums to provide type-safe constants for license types, statuses, and event types.

## Overview

The package includes three main enums:

- **LicenseType** - Available license types
- **LicenseStatus** - License status values
- **LicenseEventType** - Event types for logging

All enums are backed by string values for database storage and compatibility.

## LicenseType Enum

### Available Types

```php
namespace Akira\LaravelLicense\Enums;

enum LicenseType: string
{
    case LIFETIME = 'lifetime';
    case ANNUAL = 'annual';
    case SUBSCRIPTION = 'subscription';
    case TRIAL = 'trial';
    case CREDITS = 'credits';
}
```

### Type Descriptions

#### LIFETIME
- **Value**: `'lifetime'`
- **Description**: Permanent license with no expiration date
- **Use Case**: One-time purchase, perpetual access
- **Properties**: `expires_at` should be `null`

```php
$license = License::create([
    'type' => LicenseType::LIFETIME->value,
    'expires_at' => null,
]);
```

#### ANNUAL
- **Value**: `'annual'`
- **Description**: Annual subscription that expires after one year
- **Use Case**: Yearly renewable subscriptions
- **Properties**: `expires_at` set to one year from activation

```php
$license = License::create([
    'type' => LicenseType::ANNUAL->value,
    'expires_at' => now()->addYear(),
]);
```

#### SUBSCRIPTION
- **Value**: `'subscription'`
- **Description**: Recurring subscription-based license
- **Use Case**: Monthly/quarterly subscriptions
- **Properties**: `expires_at` set to subscription period

```php
$license = License::create([
    'type' => LicenseType::SUBSCRIPTION->value,
    'expires_at' => now()->addMonth(),
]);
```

#### TRIAL
- **Value**: `'trial'`
- **Description**: Trial period license with limited time
- **Use Case**: Free trials, evaluation periods
- **Properties**: `expires_at` set to trial duration

```php
$license = License::create([
    'type' => LicenseType::TRIAL->value,
    'expires_at' => now()->addDays(30),
]);
```

#### CREDITS
- **Value**: `'credits'`
- **Description**: Credit-based license for usage tracking
- **Use Case**: Pay-per-use, API calls, compute credits
- **Properties**: Requires `LicenseUsage` records

```php
$license = License::create([
    'type' => LicenseType::CREDITS->value,
]);

LicenseUsage::create([
    'license_id' => $license->id,
    'limit' => 10000,
    'consumed_units' => 0,
]);
```

### Using LicenseType

```php
use Akira\LaravelLicense\Enums\LicenseType;

// Get value
$value = LicenseType::ANNUAL->value; // 'annual'

// Get name
$name = LicenseType::ANNUAL->name; // 'ANNUAL'

// Create from value
$type = LicenseType::from('annual'); // LicenseType::ANNUAL

// Try from value (returns null if invalid)
$type = LicenseType::tryFrom('invalid'); // null

// Get all cases
$allTypes = LicenseType::cases(); // Array of all types

// Check equality
if ($license->typeEnum() === LicenseType::LIFETIME) {
    // Handle lifetime license
}
```

## LicenseStatus Enum

### Available Statuses

```php
namespace Akira\LaravelLicense\Enums;

enum LicenseStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case SUSPENDED = 'suspended';
    case REVOKED = 'revoked';
}
```

### Status Descriptions

#### ACTIVE
- **Value**: `'active'`
- **Description**: License is active and can be used
- **Use Case**: Normal operational state
- **Behavior**: Full access to licensed features

```php
$license->update(['status' => LicenseStatus::ACTIVE->value]);
```

#### EXPIRED
- **Value**: `'expired'`
- **Description**: License has passed its expiration date
- **Use Case**: Subscription ended, trial completed
- **Behavior**: Access should be restricted unless in grace period

```php
// Automatically set by cron job
if ($license->isExpired()) {
    $license->update(['status' => LicenseStatus::EXPIRED->value]);
}
```

#### SUSPENDED
- **Value**: `'suspended'`
- **Description**: License is temporarily suspended
- **Use Case**: Payment issues, policy violations
- **Behavior**: Temporary access restriction, can be reactivated

```php
$license->update(['status' => LicenseStatus::SUSPENDED->value]);
```

#### REVOKED
- **Value**: `'revoked'`
- **Description**: License has been permanently revoked
- **Use Case**: Fraud, refund, license abuse
- **Behavior**: Permanent access restriction

```php
$license->update(['status' => LicenseStatus::REVOKED->value]);
```

### Using LicenseStatus

```php
use Akira\LaravelLicense\Enums\LicenseStatus;

// Get value
$value = LicenseStatus::ACTIVE->value; // 'active'

// Create from value
$status = LicenseStatus::from('active'); // LicenseStatus::ACTIVE

// Check status
if ($license->statusEnum() === LicenseStatus::ACTIVE) {
    // License is active
}

// Match expression
$message = match ($license->statusEnum()) {
    LicenseStatus::ACTIVE => 'Your license is active',
    LicenseStatus::EXPIRED => 'Your license has expired',
    LicenseStatus::SUSPENDED => 'Your license is suspended',
    LicenseStatus::REVOKED => 'Your license has been revoked',
};
```

## LicenseEventType Enum

### Available Event Types

```php
namespace Akira\LaravelLicense\Enums;

enum LicenseEventType: string
{
    case CREATED = 'created';
    case ACTIVATED = 'activated';
    case DEACTIVATED = 'deactivated';
    case ROTATED = 'rotated';
    case REVOKED = 'revoked';
    case USAGE_CONSUMED = 'usage_consumed';
    case EXPIRED = 'expired';
    case ABUSE_DETECTED = 'abuse_detected';
}
```

### Event Type Descriptions

#### CREATED
- **Value**: `'created'`
- **Description**: License was created
- **When**: New license generation

```php
LicenseEvent::create([
    'license_id' => $license->id,
    'type' => LicenseEventType::CREATED->value,
    'created_at' => now(),
]);
```

#### ACTIVATED
- **Value**: `'activated'`
- **Description**: License was activated on a device/domain
- **When**: New activation created

```php
LicenseEvent::create([
    'license_id' => $license->id,
    'type' => LicenseEventType::ACTIVATED->value,
    'payload' => ['domain' => 'example.com'],
    'created_at' => now(),
]);
```

#### DEACTIVATED
- **Value**: `'deactivated'`
- **Description**: License was deactivated
- **When**: Activation removed

```php
LicenseEvent::create([
    'license_id' => $license->id,
    'type' => LicenseEventType::DEACTIVATED->value,
    'payload' => ['activation_id' => $activation->id],
    'created_at' => now(),
]);
```

#### ROTATED
- **Value**: `'rotated'`
- **Description**: License key was rotated/changed
- **When**: Security rotation or key regeneration

```php
LicenseEvent::create([
    'license_id' => $license->id,
    'type' => LicenseEventType::ROTATED->value,
    'payload' => ['old_key' => $oldKey, 'new_key' => $newKey],
    'created_at' => now(),
]);
```

#### REVOKED
- **Value**: `'revoked'`
- **Description**: License was permanently revoked
- **When**: License termination

```php
LicenseEvent::create([
    'license_id' => $license->id,
    'type' => LicenseEventType::REVOKED->value,
    'payload' => ['reason' => 'Fraud detected'],
    'created_at' => now(),
]);
```

#### USAGE_CONSUMED
- **Value**: `'usage_consumed'`
- **Description**: Usage units were consumed
- **When**: Credit/usage deduction

```php
LicenseEvent::create([
    'license_id' => $license->id,
    'type' => LicenseEventType::USAGE_CONSUMED->value,
    'payload' => ['units' => 100, 'remaining' => 900],
    'created_at' => now(),
]);
```

#### EXPIRED
- **Value**: `'expired'`
- **Description**: License expired naturally
- **When**: Expiration date reached

```php
LicenseEvent::create([
    'license_id' => $license->id,
    'type' => LicenseEventType::EXPIRED->value,
    'payload' => ['expired_at' => $license->expires_at],
    'created_at' => now(),
]);
```

#### ABUSE_DETECTED
- **Value**: `'abuse_detected'`
- **Description**: Potential license abuse detected
- **When**: Suspicious activity

```php
LicenseEvent::create([
    'license_id' => $license->id,
    'type' => LicenseEventType::ABUSE_DETECTED->value,
    'payload' => [
        'reason' => 'Multiple simultaneous activations',
        'ips' => ['192.168.1.1', '10.0.0.1'],
    ],
    'created_at' => now(),
]);
```

## Enum Utilities

### Validation

```php
// Validate enum value
function isValidLicenseType(string $type): bool
{
    return LicenseType::tryFrom($type) !== null;
}

// Validate with exception
try {
    $type = LicenseType::from($input);
} catch (\ValueError $e) {
    // Invalid type provided
}
```

### Comparison

```php
// Compare enums
$isSameType = $license1->typeEnum() === $license2->typeEnum();

// Check if active
$isActive = $license->statusEnum() === LicenseStatus::ACTIVE;

// Multiple comparisons
if (in_array($license->statusEnum(), [
    LicenseStatus::EXPIRED,
    LicenseStatus::SUSPENDED
])) {
    // License is not accessible
}
```

### Iteration

```php
// Get all license types
foreach (LicenseType::cases() as $type) {
    echo "{$type->name}: {$type->value}\n";
}

// Build select options
$options = array_map(
    fn($type) => ['value' => $type->value, 'label' => ucfirst($type->value)],
    LicenseType::cases()
);
```

### Custom Methods

You can add custom methods to enums:

```php
enum LicenseType: string
{
    case LIFETIME = 'lifetime';
    case ANNUAL = 'annual';
    // ...
    
    public function requiresExpiration(): bool
    {
        return $this !== self::LIFETIME;
    }
    
    public function supportsGracePeriod(): bool
    {
        return in_array($this, [
            self::ANNUAL,
            self::SUBSCRIPTION,
        ]);
    }
}

// Usage
if ($license->typeEnum()->requiresExpiration()) {
    // Set expiration date
}
```

## Best Practices

### 1. Always Use Enums for Type Safety

```php
// Good
$license->update(['status' => LicenseStatus::ACTIVE->value]);

// Bad
$license->update(['status' => 'active']);
```

### 2. Use Match Expressions

```php
$action = match ($license->statusEnum()) {
    LicenseStatus::ACTIVE => 'allow',
    LicenseStatus::EXPIRED => 'warn',
    LicenseStatus::SUSPENDED, LicenseStatus::REVOKED => 'deny',
};
```

### 3. Validate Input

```php
$validated = $request->validate([
    'type' => [
        'required',
        Rule::enum(LicenseType::class)
    ],
    'status' => [
        'required',
        Rule::enum(LicenseStatus::class)
    ],
]);
```

### 4. Document Custom Event Types

If you add custom event types, document them clearly in your application.

## Next Steps

Learn about:

- [Factories](07-factories.md) - Testing with factories
- [Testing](08-testing.md) - Writing tests
- [API Reference](09-api-reference.md) - Complete API documentation

---

**Navigation**: [Previous: Usage Guide](05-usage-guide.md) | [Next: Factories](07-factories.md)
