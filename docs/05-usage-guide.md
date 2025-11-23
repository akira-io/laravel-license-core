# Usage Guide

This guide provides comprehensive examples of using Laravel License in real-world scenarios.

## Table of Contents

1. [Creating Licenses](#creating-licenses)
2. [Managing Activations](#managing-activations)
3. [Tracking Usage](#tracking-usage)
4. [Event Logging](#event-logging)
5. [License Validation](#license-validation)
6. [Common Patterns](#common-patterns)

## Creating Licenses

### Basic License Creation

```php
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Enums\LicenseStatus;

$license = License::create([
    'key' => \Illuminate\Support\Str::uuid(),
    'type' => LicenseType::ANNUAL->value,
    'status' => LicenseStatus::ACTIVE->value,
    'max_activations' => 5,
    'max_seats' => 10,
]);
```

### Creating Different License Types

#### Lifetime License

```php
$license = License::create([
    'key' => \Illuminate\Support\Str::uuid(),
    'type' => LicenseType::LIFETIME->value,
    'status' => LicenseStatus::ACTIVE->value,
    'max_activations' => 1,
    'max_seats' => 1,
    'expires_at' => null, // Never expires
]);
```

#### Trial License

```php
$license = License::create([
    'key' => \Illuminate\Support\Str::uuid(),
    'type' => LicenseType::TRIAL->value,
    'status' => LicenseStatus::ACTIVE->value,
    'max_activations' => 1,
    'max_seats' => 1,
    'expires_at' => now()->addDays(30),
]);
```

#### Subscription License with Grace Period

```php
$license = License::create([
    'key' => \Illuminate\Support\Str::uuid(),
    'type' => LicenseType::SUBSCRIPTION->value,
    'status' => LicenseStatus::ACTIVE->value,
    'max_activations' => 3,
    'max_seats' => 5,
    'expires_at' => now()->addMonth(),
    'grace_ends_at' => now()->addMonth()->addDays(7),
]);
```

### Adding Metadata

```php
$license = License::create([
    'key' => \Illuminate\Support\Str::uuid(),
    'type' => LicenseType::ANNUAL->value,
    'status' => LicenseStatus::ACTIVE->value,
    'meta' => [
        'customer_id' => 12345,
        'customer_email' => 'customer@example.com',
        'plan_name' => 'Professional',
        'features' => ['api_access', 'priority_support'],
        'purchase_date' => now()->toDateString(),
    ],
]);

// Access encrypted metadata
$customerId = $license->meta['customer_id'];
```

### With Scopes

```php
$license = License::create([
    'key' => \Illuminate\Support\Str::uuid(),
    'type' => LicenseType::ANNUAL->value,
    'status' => LicenseStatus::ACTIVE->value,
    'scopes' => ['read', 'write', 'admin'],
]);

// Check scopes
if (in_array('admin', iterator_to_array($license->scopes))) {
    // User has admin scope
}
```

## Managing Activations

### Creating an Activation

```php
use Akira\LaravelLicense\Models\LicenseActivation;

// Basic activation
$activation = LicenseActivation::create([
    'license_id' => $license->id,
    'domain' => request()->getHost(),
    'machine_hash' => hash('sha256', gethostname()),
    'ip' => request()->ip(),
    'user_agent' => request()->userAgent(),
]);
```

### Checking Activation Limits

```php
function canActivate(License $license): bool
{
    $currentActivations = $license->activations()->count();
    return $currentActivations < $license->max_activations;
}

if (!canActivate($license)) {
    throw new \Exception('Maximum activations reached');
}
```

### Finding Existing Activation

```php
// Check if already activated on this machine
$existingActivation = LicenseActivation::where('license_id', $license->id)
    ->where('machine_hash', $machineHash)
    ->first();

if ($existingActivation) {
    // Already activated
    return $existingActivation;
}
```

### Deactivating

```php
// Deactivate specific activation
$activation->delete();

// Deactivate by machine hash
LicenseActivation::where('license_id', $license->id)
    ->where('machine_hash', $machineHash)
    ->delete();

// Deactivate all
$license->activations()->delete();
```

### Complete Activation Flow

```php
use Akira\LaravelLicense\Models\{License, LicenseActivation, LicenseEvent};
use Akira\LaravelLicense\Enums\LicenseEventType;

function activateLicense(string $licenseKey, string $machineHash): LicenseActivation
{
    $license = License::where('key', $licenseKey)->firstOrFail();
    
    // Validate license
    if ($license->status !== LicenseStatus::ACTIVE->value) {
        throw new \Exception('License is not active');
    }
    
    if ($license->isExpired() && !$license->inGracePeriod()) {
        throw new \Exception('License has expired');
    }
    
    // Check if already activated
    $existing = LicenseActivation::where('license_id', $license->id)
        ->where('machine_hash', $machineHash)
        ->first();
        
    if ($existing) {
        return $existing;
    }
    
    // Check activation limit
    if ($license->activations()->count() >= $license->max_activations) {
        throw new \Exception('Maximum activations reached');
    }
    
    // Create activation
    $activation = DB::transaction(function () use ($license, $machineHash) {
        $activation = LicenseActivation::create([
            'license_id' => $license->id,
            'domain' => request()->getHost(),
            'machine_hash' => $machineHash,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
        
        // Log event
        LicenseEvent::create([
            'license_id' => $license->id,
            'type' => LicenseEventType::ACTIVATED->value,
            'payload' => [
                'activation_id' => $activation->id,
                'domain' => $activation->domain,
            ],
            'created_at' => now(),
        ]);
        
        return $activation;
    });
    
    return $activation;
}
```

## Tracking Usage

### Creating Usage Record

```php
use Akira\LaravelLicense\Models\LicenseUsage;

$usage = LicenseUsage::create([
    'license_id' => $license->id,
    'consumed_units' => 0,
    'limit' => 10000,
]);
```

### Consuming Units

```php
// Increment consumed units
$usage->increment('consumed_units', 100);

// Or update directly
$usage->update([
    'consumed_units' => $usage->consumed_units + 100
]);

// Check remaining
$remaining = $usage->remaining();
```

### Complete Usage Flow

```php
use Akira\LaravelLicense\Models\{LicenseUsage, LicenseEvent};
use Akira\LaravelLicense\Enums\LicenseEventType;

function consumeUnits(License $license, int $units): void
{
    $usage = LicenseUsage::firstOrCreate(
        ['license_id' => $license->id],
        ['consumed_units' => 0, 'limit' => 10000]
    );
    
    // Check if enough units available
    if ($usage->remaining() < $units) {
        throw new \Exception('Insufficient units available');
    }
    
    DB::transaction(function () use ($usage, $units, $license) {
        // Consume units
        $usage->increment('consumed_units', $units);
        
        // Log event
        LicenseEvent::create([
            'license_id' => $license->id,
            'type' => LicenseEventType::USAGE_CONSUMED->value,
            'payload' => [
                'units_consumed' => $units,
                'remaining' => $usage->remaining(),
                'timestamp' => now()->toIso8601String(),
            ],
            'created_at' => now(),
        ]);
        
        // Warning if running low
        if ($usage->remaining() < 100) {
            // Notify user about low credits
            event(new LowCreditsWarning($license, $usage));
        }
    });
}
```

### Resetting Usage

```php
// Reset usage for a new period
$usage->update([
    'consumed_units' => 0,
]);

// Or create new usage record
LicenseUsage::create([
    'license_id' => $license->id,
    'consumed_units' => 0,
    'limit' => 10000,
]);
```

## Event Logging

### Logging Different Events

```php
use Akira\LaravelLicense\Models\LicenseEvent;
use Akira\LaravelLicense\Enums\LicenseEventType;

// License created
LicenseEvent::create([
    'license_id' => $license->id,
    'type' => LicenseEventType::CREATED->value,
    'payload' => [
        'created_by' => auth()->id(),
        'ip' => request()->ip(),
    ],
    'created_at' => now(),
]);

// License activated
LicenseEvent::create([
    'license_id' => $license->id,
    'type' => LicenseEventType::ACTIVATED->value,
    'payload' => [
        'activation_id' => $activation->id,
        'domain' => $activation->domain,
    ],
    'created_at' => now(),
]);

// License expired
LicenseEvent::create([
    'license_id' => $license->id,
    'type' => LicenseEventType::EXPIRED->value,
    'payload' => [
        'expired_at' => $license->expires_at->toIso8601String(),
    ],
    'created_at' => now(),
]);

// Abuse detected
LicenseEvent::create([
    'license_id' => $license->id,
    'type' => LicenseEventType::ABUSE_DETECTED->value,
    'payload' => [
        'reason' => 'Multiple simultaneous activations',
        'details' => '10 activations in 5 minutes',
        'ip_addresses' => ['192.168.1.1', '10.0.0.1'],
    ],
    'created_at' => now(),
]);
```

### Querying Events

```php
// Get all events for a license
$events = $license->events()->orderBy('created_at', 'desc')->get();

// Get recent events
$recentEvents = $license->events()
    ->where('created_at', '>', now()->subDay())
    ->get();

// Get specific event type
$activations = $license->events()
    ->where('type', LicenseEventType::ACTIVATED->value)
    ->get();

// Count events by type
$eventCounts = $license->events()
    ->select('type', DB::raw('count(*) as count'))
    ->groupBy('type')
    ->get();
```

## License Validation

### Complete Validation Function

```php
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Enums\LicenseStatus;

function validateLicense(string $licenseKey): array
{
    $license = License::where('key', $licenseKey)->first();
    
    if (!$license) {
        return [
            'valid' => false,
            'reason' => 'License not found'
        ];
    }
    
    // Check status
    if ($license->status !== LicenseStatus::ACTIVE->value) {
        return [
            'valid' => false,
            'reason' => "License is {$license->status}"
        ];
    }
    
    // Check expiration
    if ($license->isExpired()) {
        if ($license->inGracePeriod()) {
            return [
                'valid' => true,
                'warning' => 'License expired but in grace period',
                'grace_ends_at' => $license->grace_ends_at
            ];
        }
        
        return [
            'valid' => false,
            'reason' => 'License has expired'
        ];
    }
    
    // Check activation limit
    $activationCount = $license->activations()->count();
    if ($activationCount >= $license->max_activations) {
        return [
            'valid' => false,
            'reason' => 'Maximum activations reached'
        ];
    }
    
    return [
        'valid' => true,
        'license' => $license,
        'activations_remaining' => $license->max_activations - $activationCount
    ];
}
```

### Validation Middleware

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateLicense
{
    public function handle(Request $request, Closure $next)
    {
        $licenseKey = $request->header('X-License-Key');
        
        if (!$licenseKey) {
            return response()->json(['error' => 'License key required'], 401);
        }
        
        $validation = validateLicense($licenseKey);
        
        if (!$validation['valid']) {
            return response()->json([
                'error' => $validation['reason']
            ], 403);
        }
        
        // Add license to request
        $request->attributes->add(['license' => $validation['license']]);
        
        return $next($request);
    }
}
```

## Common Patterns

### License Renewal

```php
function renewLicense(License $license, int $months = 12): void
{
    DB::transaction(function () use ($license, $months) {
        $newExpiresAt = $license->expires_at 
            ? $license->expires_at->addMonths($months)
            : now()->addMonths($months);
            
        $license->update([
            'expires_at' => $newExpiresAt,
            'grace_ends_at' => $newExpiresAt->copy()->addWeek(),
            'status' => LicenseStatus::ACTIVE->value,
        ]);
        
        LicenseEvent::create([
            'license_id' => $license->id,
            'type' => 'renewed',
            'payload' => [
                'renewed_at' => now()->toIso8601String(),
                'new_expiry' => $newExpiresAt->toIso8601String(),
            ],
            'created_at' => now(),
        ]);
    });
}
```

### License Suspension

```php
function suspendLicense(License $license, string $reason): void
{
    DB::transaction(function () use ($license, $reason) {
        $license->update([
            'status' => LicenseStatus::SUSPENDED->value,
        ]);
        
        LicenseEvent::create([
            'license_id' => $license->id,
            'type' => 'suspended',
            'payload' => [
                'reason' => $reason,
                'suspended_by' => auth()->id(),
            ],
            'created_at' => now(),
        ]);
    });
}
```

### Upgrade License

```php
function upgradeLicense(License $license, array $newLimits): void
{
    $oldLimits = [
        'max_activations' => $license->max_activations,
        'max_seats' => $license->max_seats,
    ];
    
    DB::transaction(function () use ($license, $newLimits, $oldLimits) {
        $license->update($newLimits);
        
        LicenseEvent::create([
            'license_id' => $license->id,
            'type' => 'upgraded',
            'payload' => [
                'old_limits' => $oldLimits,
                'new_limits' => $newLimits,
            ],
            'created_at' => now(),
        ]);
    });
}
```

## Next Steps

Learn more about:

- [Enums](06-enums.md) - Available enumerations
- [Factories](07-factories.md) - Testing with factories
- [Testing](08-testing.md) - Writing tests

---

**Navigation**: [Previous: Models](04-models.md) | [Next: Enums](06-enums.md)
