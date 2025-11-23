# Models

This guide covers all models provided by Laravel License and their usage.

## Overview

The package includes four main Eloquent models:

- **License** - Core model representing a software license
- **LicenseActivation** - Tracks license activations on devices/domains
- **LicenseUsage** - Monitors usage for credit-based licenses
- **LicenseEvent** - Logs events for audit trail

All models follow Laravel conventions and integrate seamlessly with Eloquent.

## License Model

The License model is the core of the licensing system.

### Properties

```php
namespace Akira\LaravelLicense\Models;

final class License extends Model
{
    // Primary key
    public int $id;
    
    // License key (UUID)
    public string $key;
    
    // License type (see LicenseType enum)
    public string $type;
    
    // Current status (see LicenseStatus enum)
    public string $status;
    
    // Maximum number of activations allowed
    public int $max_activations;
    
    // Maximum number of seats/users allowed
    public int $max_seats;
    
    // Whether this license can be used as fallback
    public bool $fallback;
    
    // Permission scopes (array, stored as JSON)
    public ?array $scopes;
    
    // Encrypted metadata (array, encrypted in database)
    public ?array $meta;
    
    // Expiration date
    public ?CarbonInterface $expires_at;
    
    // Grace period end date
    public ?CarbonInterface $grace_ends_at;
    
    // Creation timestamp
    public CarbonInterface $created_at;
    
    // Last update timestamp
    public CarbonInterface $updated_at;
}
```

### Relationships

```php
// Has many activations
$license->activations // Collection of LicenseActivation

// Has many usage records
$license->usages // Collection of LicenseUsage

// Has many events
$license->events // Collection of LicenseEvent
```

### Methods

#### typeEnum()

Returns the license type as an enum:

```php
$type = $license->typeEnum(); // Returns LicenseType enum

if ($type === LicenseType::LIFETIME) {
    // Handle lifetime license
}
```

#### statusEnum()

Returns the license status as an enum:

```php
$status = $license->statusEnum(); // Returns LicenseStatus enum

if ($status === LicenseStatus::ACTIVE) {
    // License is active
}
```

#### isExpired()

Checks if the license has expired:

```php
if ($license->isExpired()) {
    // License is past its expiration date
    // Note: Does not check grace period
}
```

Returns `false` if `expires_at` is `null`.

#### inGracePeriod()

Checks if the license is currently in grace period:

```php
if ($license->inGracePeriod()) {
    // License is expired but within grace period
    // Show warning to user
}
```

Returns `true` only if current time is between `expires_at` and `grace_ends_at`.

### Usage Example

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
    'expires_at' => now()->addYear(),
    'grace_ends_at' => now()->addYear()->addWeek(),
    'meta' => [
        'customer_id' => 123,
        'plan' => 'professional',
    ],
]);
```

## LicenseActivation Model

Tracks activations of licenses on specific devices or domains.

### Properties

```php
namespace Akira\LaravelLicense\Models;

final class LicenseActivation extends Model
{
    // Primary key
    public int $id;
    
    // Foreign key to licenses table
    public int $license_id;
    
    // Domain where license is activated
    public ?string $domain;
    
    // Unique machine identifier hash
    public ?string $machine_hash;
    
    // IP address of activation
    public ?string $ip;
    
    // User agent string
    public ?string $user_agent;
    
    // Activation timestamp
    public CarbonInterface $created_at;
    
    // Last update timestamp
    public CarbonInterface $updated_at;
}
```

### Relationships

```php
// Belongs to a license
$activation->license // License instance
```

### Usage Example

```php
use Akira\LaravelLicense\Models\LicenseActivation;

$activation = LicenseActivation::create([
    'license_id' => $license->id,
    'domain' => request()->getHost(),
    'machine_hash' => hash('sha256', gethostname()),
    'ip' => request()->ip(),
    'user_agent' => request()->userAgent(),
]);

// Get the license
$license = $activation->license;
```

## LicenseUsage Model

Monitors usage for credit-based or usage-limited licenses.

### Properties

```php
namespace Akira\LaravelLicense\Models;

final class LicenseUsage extends Model
{
    // Primary key
    public int $id;
    
    // Foreign key to licenses table
    public int $license_id;
    
    // Number of units consumed
    public int $consumed_units;
    
    // Maximum allowed units
    public int $limit;
    
    // Creation timestamp
    public CarbonInterface $created_at;
    
    // Last update timestamp
    public CarbonInterface $updated_at;
}
```

### Relationships

```php
// Belongs to a license
$usage->license // License instance
```

### Methods

#### remaining()

Calculates remaining available units:

```php
$remaining = $usage->remaining(); // Returns int

// Returns 0 if consumed_units >= limit
// Returns (limit - consumed_units) otherwise
```

### Usage Example

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
$usage->increment('consumed_units', 250);
$remaining = $usage->remaining(); // 750

// Check if depleted
if ($usage->remaining() === 0) {
    // No units remaining
}
```

## LicenseEvent Model

Logs events for complete audit trail of license actions.

### Properties

```php
namespace Akira\LaravelLicense\Models;

final class LicenseEvent extends Model
{
    // Note: $timestamps = false, only uses created_at
    
    // Primary key
    public int $id;
    
    // Foreign key to licenses table
    public int $license_id;
    
    // Event type (see LicenseEventType enum)
    public string $type;
    
    // Event data (array, stored as JSON ArrayObject)
    public ?ArrayObject $payload;
    
    // Event timestamp
    public CarbonInterface $created_at;
}
```

### Relationships

```php
// Belongs to a license
$event->license // License instance
```

### Usage Example

```php
use Akira\LaravelLicense\Models\LicenseEvent;
use Akira\LaravelLicense\Enums\LicenseEventType;

$event = LicenseEvent::create([
    'license_id' => $license->id,
    'type' => LicenseEventType::ACTIVATED->value,
    'payload' => [
        'domain' => 'example.com',
        'ip' => request()->ip(),
        'user_id' => auth()->id(),
    ],
    'created_at' => now(),
]);

// Access payload data
$domain = $event->payload['domain'];
```

## Working with Relationships

### Eager Loading

To avoid N+1 queries, use eager loading:

```php
// Load license with all relationships
$license = License::with(['activations', 'usages', 'events'])
    ->find($id);

// Load specific relationships
$license = License::with('activations')
    ->find($id);

// Conditional eager loading
$licenses = License::when($includeActivations, function ($query) {
    $query->with('activations');
})->get();
```

### Counting Relationships

Get count without loading all records:

```php
// Count activations
$activationCount = $license->activations()->count();

// Load license with activation count
$license = License::withCount('activations')->find($id);
echo $license->activations_count;
```

### Querying Relationships

```php
// Get licenses with at least 1 activation
$licenses = License::has('activations')->get();

// Get licenses with 5 or more activations
$licenses = License::has('activations', '>=', 5)->get();

// Get licenses with active activations (custom scope)
$licenses = License::whereHas('activations', function ($query) {
    $query->where('created_at', '>', now()->subMonth());
})->get();
```

## Scopes and Query Builders

### Custom Scopes

You can add custom scopes to models:

```php
// In your custom License model
public function scopeActive($query)
{
    return $query->where('status', LicenseStatus::ACTIVE->value);
}

public function scopeExpired($query)
{
    return $query->where('expires_at', '<', now());
}

// Usage
$activeLicenses = License::active()->get();
$expiredLicenses = License::expired()->get();
```

### Query Examples

```php
// Find by key
$license = License::where('key', $key)->first();

// Get active annual licenses
$licenses = License::where('status', LicenseStatus::ACTIVE->value)
    ->where('type', LicenseType::ANNUAL->value)
    ->get();

// Get licenses expiring soon
$expiringLicenses = License::whereBetween('expires_at', [
    now(),
    now()->addWeek()
])->get();

// Get licenses by activation count
$mostActivated = License::withCount('activations')
    ->orderBy('activations_count', 'desc')
    ->take(10)
    ->get();
```

## Model Events

You can observe model events for custom logic:

```php
// In a service provider
use Akira\LaravelLicense\Models\License;

License::created(function ($license) {
    // Log license creation
    Log::info("License created: {$license->key}");
});

License::updated(function ($license) {
    if ($license->isDirty('status')) {
        // Status changed
        Log::info("License status changed to: {$license->status}");
    }
});
```

## Best Practices

### 1. Always Validate Before Creating

```php
$validated = $request->validate([
    'type' => 'required|in:lifetime,annual,subscription,trial,credits',
    'max_activations' => 'required|integer|min:1',
]);

$license = License::create($validated);
```

### 2. Use Transactions for Complex Operations

```php
DB::transaction(function () use ($license) {
    $activation = LicenseActivation::create([
        'license_id' => $license->id,
        'domain' => 'example.com',
    ]);
    
    LicenseEvent::create([
        'license_id' => $license->id,
        'type' => LicenseEventType::ACTIVATED->value,
        'created_at' => now(),
    ]);
});
```

### 3. Index Important Columns

If you extend models, add indexes for frequently queried columns:

```php
Schema::table('licenses', function (Blueprint $table) {
    $table->index('status');
    $table->index('expires_at');
});
```

### 4. Use Accessor/Mutators for Data Transformation

```php
// In your custom model
protected function key(): Attribute
{
    return Attribute::make(
        get: fn ($value) => strtoupper($value),
    );
}
```

## Next Steps

Learn more about:

- [Usage Guide](05-usage-guide.md) - Complete usage examples
- [Enums](06-enums.md) - Understanding enums
- [Factories](07-factories.md) - Testing with factories

---

**Navigation**: [Previous: Configuration](03-configuration.md) | [Next: Usage Guide](05-usage-guide.md)
