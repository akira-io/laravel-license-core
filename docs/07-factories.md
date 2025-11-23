# Factories

Laravel License includes comprehensive Eloquent factories for all models, making testing and development easier.

## Overview

The package provides factories for:

- **LicenseFactory** - Create test licenses
- **LicenseActivationFactory** - Create test activations
- **LicenseUsageFactory** - Create test usage records
- **LicenseEventFactory** - Create test events

All factories support custom states and relationships.

## LicenseFactory

### Basic Usage

```php
use Akira\LaravelLicense\Models\License;

// Create a single license
$license = License::factory()->create();

// Create multiple licenses
$licenses = License::factory()->count(5)->create();

// Make without persisting
$license = License::factory()->make();
```

### Available States

#### Status States

```php
// Active license (default)
$license = License::factory()->active()->create();

// Expired license
$license = License::factory()->expired()->create();

// Suspended license
$license = License::factory()->suspended()->create();

// Revoked license
$license = License::factory()->revoked()->create();
```

#### Type States

```php
// Lifetime license
$license = License::factory()->lifetime()->create();

// Annual license
$license = License::factory()->annual()->create();

// Trial license
$license = License::factory()->trial()->create();
```

#### Special States

```php
// License with grace period
$license = License::factory()->withGracePeriod()->create();

// License with custom metadata
$license = License::factory()
    ->withMeta(['customer_id' => 123, 'plan' => 'pro'])
    ->create();
```

### Combining States

```php
// Active annual license
$license = License::factory()
    ->active()
    ->annual()
    ->create();

// Expired license with grace period
$license = License::factory()
    ->expired()
    ->withGracePeriod()
    ->create();

// Trial license with metadata
$license = License::factory()
    ->trial()
    ->withMeta(['source' => 'website'])
    ->create();
```

### Custom Attributes

```php
// Override specific attributes
$license = License::factory()->create([
    'key' => 'CUSTOM-KEY-123',
    'max_activations' => 10,
    'max_seats' => 20,
]);

// Use state with custom attributes
$license = License::factory()
    ->annual()
    ->create([
        'max_activations' => 3,
    ]);
```

### With Relationships

```php
// License with activations
$license = License::factory()
    ->has(LicenseActivation::factory()->count(3), 'activations')
    ->create();

// License with all relationships
$license = License::factory()
    ->has(LicenseActivation::factory()->count(2), 'activations')
    ->has(LicenseEvent::factory()->count(5), 'events')
    ->has(LicenseUsage::factory(), 'usages')
    ->create();
```

## LicenseActivationFactory

### Basic Usage

```php
use Akira\LaravelLicense\Models\LicenseActivation;

// Create activation (creates license automatically)
$activation = LicenseActivation::factory()->create();

// Create activation for specific license
$activation = LicenseActivation::factory()
    ->forLicense($license)
    ->create();
```

### Available Methods

#### forLicense()

Associate activation with specific license:

```php
$license = License::factory()->create();

$activation = LicenseActivation::factory()
    ->forLicense($license)
    ->create();
```

#### withDomain()

Set specific domain:

```php
$activation = LicenseActivation::factory()
    ->withDomain('example.com')
    ->create();
```

#### withMachineHash()

Set specific machine hash:

```php
$activation = LicenseActivation::factory()
    ->withMachineHash('abc123def456')
    ->create();
```

#### withIp()

Set specific IP address:

```php
$activation = LicenseActivation::factory()
    ->withIp('192.168.1.100')
    ->create();
```

#### withIpv6()

Generate IPv6 address:

```php
$activation = LicenseActivation::factory()
    ->withIpv6()
    ->create();
```

### Combining Methods

```php
$activation = LicenseActivation::factory()
    ->forLicense($license)
    ->withDomain('example.com')
    ->withMachineHash(hash('sha256', 'machine-123'))
    ->withIp('192.168.1.1')
    ->create();
```

### Multiple Activations

```php
// Create multiple activations for same license
$activations = LicenseActivation::factory()
    ->count(3)
    ->forLicense($license)
    ->create();

// Different domains
foreach (['site1.com', 'site2.com', 'site3.com'] as $domain) {
    LicenseActivation::factory()
        ->forLicense($license)
        ->withDomain($domain)
        ->create();
}
```

## LicenseUsageFactory

### Basic Usage

```php
use Akira\LaravelLicense\Models\LicenseUsage;

// Create usage record
$usage = LicenseUsage::factory()->create();

// Create for specific license
$usage = LicenseUsage::factory()
    ->forLicense($license)
    ->create();
```

### Available Methods

#### forLicense()

Associate with specific license:

```php
$usage = LicenseUsage::factory()
    ->forLicense($license)
    ->create();
```

#### withLimit()

Set specific limit:

```php
$usage = LicenseUsage::factory()
    ->withLimit(10000)
    ->create();
```

#### withConsumed()

Set consumed units:

```php
$usage = LicenseUsage::factory()
    ->withConsumed(2500)
    ->create();
```

#### fresh()

Create usage with zero consumption:

```php
$usage = LicenseUsage::factory()
    ->fresh()
    ->create();

// consumed_units = 0
```

#### depleted()

Create usage at limit:

```php
$usage = LicenseUsage::factory()
    ->depleted()
    ->create();

// consumed_units = limit
// remaining() returns 0
```

#### overLimit()

Create usage over limit:

```php
$usage = LicenseUsage::factory()
    ->overLimit()
    ->create();

// consumed_units > limit
// remaining() returns 0
```

### Usage Scenarios

```php
// Fresh license with 1000 units
$usage = LicenseUsage::factory()
    ->forLicense($license)
    ->withLimit(1000)
    ->fresh()
    ->create();

// Partially consumed
$usage = LicenseUsage::factory()
    ->withLimit(5000)
    ->withConsumed(2000)
    ->create();

// Nearly depleted
$usage = LicenseUsage::factory()
    ->withLimit(100)
    ->withConsumed(95)
    ->create();
```

## LicenseEventFactory

### Basic Usage

```php
use Akira\LaravelLicense\Models\LicenseEvent;

// Create event
$event = LicenseEvent::factory()->create();

// Create for specific license
$event = LicenseEvent::factory()
    ->forLicense($license)
    ->create();
```

### Available Methods

#### forLicense()

Associate with specific license:

```php
$event = LicenseEvent::factory()
    ->forLicense($license)
    ->create();
```

#### Event Type Methods

```php
// Created event
$event = LicenseEvent::factory()->created()->create();

// Activated event
$event = LicenseEvent::factory()->activated()->create();

// Deactivated event
$event = LicenseEvent::factory()->deactivated()->create();

// Rotated event
$event = LicenseEvent::factory()->rotated()->create();

// Revoked event
$event = LicenseEvent::factory()->revoked()->create();

// Expired event
$event = LicenseEvent::factory()->expired()->create();
```

#### Payload Methods

```php
// With specific payload
$event = LicenseEvent::factory()
    ->withPayload(['ip' => '192.168.1.1', 'user_id' => 123])
    ->create();

// Without payload
$event = LicenseEvent::factory()
    ->withoutPayload()
    ->create();
```

### Creating Event History

```php
$license = License::factory()->create();

// Create event history
LicenseEvent::factory()
    ->forLicense($license)
    ->created()
    ->create(['created_at' => now()->subDays(10)]);

LicenseEvent::factory()
    ->forLicense($license)
    ->activated()
    ->create(['created_at' => now()->subDays(9)]);

LicenseEvent::factory()
    ->forLicense($license)
    ->count(5)
    ->create();
```

## Testing Scenarios

### Complete License with History

```php
$license = License::factory()
    ->annual()
    ->active()
    ->create();

// Add activations
LicenseActivation::factory()
    ->count(2)
    ->forLicense($license)
    ->create();

// Add usage
LicenseUsage::factory()
    ->forLicense($license)
    ->withLimit(1000)
    ->withConsumed(500)
    ->create();

// Add event history
LicenseEvent::factory()->forLicense($license)->created()->create();
LicenseEvent::factory()->forLicense($license)->activated()->create();
```

### Testing Expiration

```php
// Expired license
$license = License::factory()
    ->expired()
    ->create();

assertTrue($license->isExpired());
assertFalse($license->inGracePeriod());

// Expired in grace period
$license = License::factory()
    ->expired()
    ->withGracePeriod()
    ->create();

assertTrue($license->isExpired());
assertTrue($license->inGracePeriod());
```

### Testing Activation Limits

```php
$license = License::factory()
    ->create(['max_activations' => 3]);

// Create maximum activations
LicenseActivation::factory()
    ->count(3)
    ->forLicense($license)
    ->create();

// Assert limit reached
assertEquals(3, $license->activations()->count());
assertEquals($license->max_activations, $license->activations()->count());
```

### Testing Usage Limits

```php
$usage = LicenseUsage::factory()
    ->withLimit(100)
    ->withConsumed(90)
    ->create();

assertEquals(10, $usage->remaining());

// Deplete
$usage = LicenseUsage::factory()
    ->depleted()
    ->create();

assertEquals(0, $usage->remaining());
```

## Best Practices

### 1. Use Factories in Tests

```php
test('can create license', function () {
    $license = License::factory()->create();
    
    assertDatabaseHas('licenses', [
        'id' => $license->id,
        'status' => 'active',
    ]);
});
```

### 2. Create Realistic Test Data

```php
$license = License::factory()
    ->annual()
    ->create([
        'max_activations' => 5,
        'expires_at' => now()->addYear(),
    ]);
```

### 3. Test Edge Cases

```php
// Test over-limit scenario
$usage = LicenseUsage::factory()
    ->overLimit()
    ->create();

assertEquals(0, $usage->remaining());
```

### 4. Use Relationships

```php
$license = License::factory()
    ->has(LicenseActivation::factory()->count(3), 'activations')
    ->create();

assertCount(3, $license->activations);
```

## Seeding Development Data

Use factories in seeders:

```php
// database/seeders/LicenseSeeder.php
namespace Database\Seeders;

use Akira\LaravelLicense\Models\License;
use Illuminate\Database\Seeder;

class LicenseSeeder extends Seeder
{
    public function run()
    {
        // Create active licenses
        License::factory()
            ->count(10)
            ->active()
            ->annual()
            ->create();

        // Create trial licenses
        License::factory()
            ->count(5)
            ->trial()
            ->create();

        // Create license with full history
        $license = License::factory()
            ->has(LicenseActivation::factory()->count(2), 'activations')
            ->has(LicenseUsage::factory(), 'usages')
            ->has(LicenseEvent::factory()->count(5), 'events')
            ->create();
    }
}
```

## Next Steps

Learn about:

- [Testing](08-testing.md) - Comprehensive testing guide
- Test examples in the `tests/` directory

---

**Navigation**: [Previous: Enums](06-enums.md) | [Next: Testing](08-testing.md)
