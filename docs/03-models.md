# Models & Data

Reference guide for all Eloquent models in Laravel License Core.

## Overview

Laravel License Core provides 4 main models for managing licenses:

| Model | Purpose |
|-------|---------|
| **License** | Core license records |
| **LicenseActivation** | Machine/domain activations |
| **LicenseUsage** | Credit consumption tracking |
| **LicenseEvent** | Audit log of all events |

---

## License Model

The main model representing a software license.

### Properties

```php
class License extends Model
{
    public string $key;                    // Unique license key (e.g., 'LIC-xxx')
    public LicenseType $type;              // Type: lifetime, annual, subscription, trial, credits
    public LicenseStatus $status;          // Status: active, expired, suspended, revoked
    public int $max_activations;           // Max machines that can activate
    public int $max_seats;                 // Max concurrent users
    public bool $fallback;                 // Continue after expiration (annual only)
    public array $scopes;                  // Feature permissions
    public array $meta;                    // Encrypted custom data
    public ?CarbonInterface $expires_at;   // Expiration date (null = never)
    public ?CarbonInterface $grace_ends_at;// Grace period end date
    public ?CarbonInterface $created_at;   // Created timestamp
    public ?CarbonInterface $updated_at;   // Updated timestamp
}
```

### Creating Licenses

```php
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Enums\LicenseStatus;

// Basic license
$license = License::create([
    'key' => 'LIC-' . Str::uuid(),
    'type' => LicenseType::ANNUAL,
    'status' => LicenseStatus::ACTIVE,
    'max_activations' => 5,
    'max_seats' => 10,
    'expires_at' => now()->addYear(),
]);

// With metadata
$license = License::create([
    'key' => 'LIC-' . Str::uuid(),
    'type' => LicenseType::SUBSCRIPTION,
    'status' => LicenseStatus::ACTIVE,
    'meta' => [
        'customer_id' => 123,
        'customer_email' => 'john@example.com',
        'plan_name' => 'professional',
        'features' => ['api_access', 'priority_support'],
    ],
    'expires_at' => now()->addMonth(),
    'grace_ends_at' => now()->addMonth()->addDays(30),
]);

// With scopes
$license = License::create([
    'key' => 'LIC-' . Str::uuid(),
    'type' => LicenseType::ANNUAL,
    'scopes' => ['read', 'write', 'api'],
]);
```

### Methods

#### Status Checks

```php
// Check status
$license->typeEnum(): LicenseType;        // Get enum instance
$license->statusEnum(): LicenseStatus;    // Get enum instance

// Expiration checks
$license->isExpired(): bool;              // Is past expiration date?
$license->inGracePeriod(): bool;          // Is in grace period?
$license->isActive(): bool;               // Status is ACTIVE?
```

#### Access Encrypted Data

```php
// Access encrypted metadata
$customerId = $license->meta['customer_id'];
$features = $license->meta['features'] ?? [];

// Update metadata
$license->update([
    'meta' => array_merge($license->meta, [
        'last_validated_at' => now(),
    ]),
]);
```

#### Access Scopes

```php
// Check scopes
if (in_array('api', $license->scopes)) {
    // User has API access
}

// Update scopes
$license->update([
    'scopes' => ['read', 'write', 'api', 'admin'],
]);
```

### Relations

```php
// Get all activations
$activations = $license->activations;     // Collection of LicenseActivation

// Get specific activation
$activation = $license->activations()
    ->where('machine_hash', $hash)
    ->first();

// Get usage records
$usages = $license->usages;               // Collection of LicenseUsage

// Get audit events
$events = $license->events;               // Collection of LicenseEvent
```

### Querying

```php
// Find by key
$license = License::where('key', 'LIC-xxx')->first();

// Get active licenses
$active = License::where('status', LicenseStatus::ACTIVE)->get();

// Get expired licenses
$expired = License::where('expires_at', '<', now())->get();

// Get licenses with activations
$withActivations = License::has('activations')->get();

// Get licenses by type
$annual = License::where('type', LicenseType::ANNUAL)->get();
```

---

## LicenseActivation Model

Tracks where and when licenses are activated.

### Properties

```php
class LicenseActivation extends Model
{
    public int $license_id;              // Foreign key to License
    public string $domain;               // Domain where activated (e.g., 'api.example.com')
    public string $machine_hash;         // SHA256 hash of machine identifier
    public ?string $ip;                  // IP address of activation
    public ?string $user_agent;          // Browser/app user agent
    public ?CarbonInterface $expires_at; // Optional activation expiry
    public ?CarbonInterface $created_at;
    public ?CarbonInterface $updated_at;
}
```

### Creating Activations

```php
use Akira\LaravelLicense\Models\LicenseActivation;

$activation = LicenseActivation::create([
    'license_id' => $license->id,
    'domain' => request()->getHost(),
    'machine_hash' => hash('sha256', gethostname()),
    'ip' => request()->ip(),
    'user_agent' => request()->userAgent(),
]);
```

### Methods

```php
// Get the license
$license = $activation->license;

// Check if expired
$activation->isExpired(): bool;

// Update activation
$activation->update([
    'ip' => request()->ip(),
]);
```

### Querying

```php
// Find activations for a license
$activations = LicenseActivation::where('license_id', $license->id)->get();

// Find specific machine activation
$activation = LicenseActivation::where('license_id', $license->id)
    ->where('machine_hash', $hash)
    ->first();

// Get all activations on a domain
$domain_activations = LicenseActivation::where('domain', 'api.example.com')->get();

// Get recent activations
$recent = LicenseActivation::where('created_at', '>', now()->subDay())
    ->get();
```

### Managing Activations

```php
// Delete an activation (deactivate)
$activation->delete();

// Deactivate by machine hash
LicenseActivation::where('license_id', $license->id)
    ->where('machine_hash', $hash)
    ->delete();

// Deactivate all activations for a license
$license->activations()->delete();

// Count activations
$count = $license->activations()->count();
```

---

## LicenseUsage Model

Tracks credit consumption for credit-based licenses.

### Properties

```php
class LicenseUsage extends Model
{
    public int $license_id;              // Foreign key to License
    public int $consumed_units;          // Units already consumed
    public int $limit;                   // Total units available
    public ?CarbonInterface $created_at;
    public ?CarbonInterface $updated_at;
}
```

### Creating Usage Records

```php
use Akira\LaravelLicense\Models\LicenseUsage;

$usage = LicenseUsage::create([
    'license_id' => $license->id,
    'consumed_units' => 0,
    'limit' => 10000,
]);
```

### Methods

```php
// Get the license
$license = $usage->license;

// Get remaining units
$remaining = $usage->remaining(): int;

// Check if depleted
$usage->isDepleted(): bool;

// Get percentage used
$percent = ($usage->consumed_units / $usage->limit) * 100;
```

### Consuming Credits

```php
// Increment consumption
$usage->increment('consumed_units', 100);

// Or update directly
$usage->update([
    'consumed_units' => $usage->consumed_units + 100,
]);

// Reset consumption
$usage->update([
    'consumed_units' => 0,
]);

// Refund credits
$usage->update([
    'consumed_units' => DB::raw('consumed_units - 100'),
]);
```

### Querying

```php
// Get usage for a license
$usage = LicenseUsage::where('license_id', $license->id)->first();

// Find depleted licenses
$depleted = LicenseUsage::whereRaw('consumed_units >= limit')->get();

// Find nearly depleted
$almostDepleted = LicenseUsage::whereRaw('consumed_units >= limit * 0.9')
    ->get();

// Get total consumed across all licenses
$total = LicenseUsage::sum('consumed_units');
```

---

## LicenseEvent Model

Audit log of all license events.

### Properties

```php
class LicenseEvent extends Model
{
    public int $license_id;              // Foreign key to License
    public string $type;                 // Event type (see LicenseEventType enum)
    public array $payload;               // JSON event data
    public ?CarbonInterface $created_at; // Only timestamp (no updated_at)
}
```

### Event Types

Available event types:

```php
enum LicenseEventType: string
{
    case CREATED = 'created';
    case ACTIVATED = 'activated';
    case DEACTIVATED = 'deactivated';
    case ROTATED = 'rotated';
    case REVOKED = 'revoked';
    case SUSPENDED = 'suspended';
    case USAGE_CONSUMED = 'usage_consumed';
    case EXPIRED = 'expired';
    case ABUSE_DETECTED = 'abuse_detected';
}
```

### Logging Events

```php
use Akira\LaravelLicense\Models\LicenseEvent;
use Akira\LaravelLicense\Enums\LicenseEventType;

// License created
LicenseEvent::create([
    'license_id' => $license->id,
    'type' => LicenseEventType::CREATED,
    'payload' => [
        'created_by' => auth()->id(),
        'plan' => 'professional',
    ],
]);

// License activated
LicenseEvent::create([
    'license_id' => $license->id,
    'type' => LicenseEventType::ACTIVATED,
    'payload' => [
        'domain' => 'api.example.com',
        'machine_hash' => $hash,
        'ip' => request()->ip(),
    ],
]);

// Credits consumed
LicenseEvent::create([
    'license_id' => $license->id,
    'type' => LicenseEventType::USAGE_CONSUMED,
    'payload' => [
        'units_consumed' => 100,
        'remaining' => 9900,
        'timestamp' => now()->toIso8601String(),
    ],
]);

// Abuse detected
LicenseEvent::create([
    'license_id' => $license->id,
    'type' => LicenseEventType::ABUSE_DETECTED,
    'payload' => [
        'reason' => 'Rapid activations detected',
        'activations_in_window' => 10,
        'window_minutes' => 10,
    ],
]);
```

### Methods

```php
// Get the license
$license = $event->license;

// Access event data
$type = $event->type;           // Event type string
$payload = $event->payload;     // Array of event details
$createdAt = $event->created_at;
```

### Querying

```php
// Get all events for a license
$events = LicenseEvent::where('license_id', $license->id)
    ->orderBy('created_at', 'desc')
    ->get();

// Get specific event type
$activations = LicenseEvent::where('license_id', $license->id)
    ->where('type', LicenseEventType::ACTIVATED)
    ->get();

// Get recent events
$recent = LicenseEvent::where('created_at', '>', now()->subDay())
    ->get();

// Count activations
$activationCount = LicenseEvent::where('license_id', $license->id)
    ->where('type', LicenseEventType::ACTIVATED)
    ->count();

// Get events by type
$eventsByType = LicenseEvent::where('license_id', $license->id)
    ->select('type', DB::raw('count(*) as count'))
    ->groupBy('type')
    ->get();
```

---

## Working with Models

### Transactions

Use transactions for multiple model operations:

```php
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    // Create license
    $license = License::create([...]);

    // Create activation
    $license->activations()->create([...]);

    // Log event
    LicenseEvent::create([...]);
});
```

### Mass Operations

```php
// Update multiple licenses
License::where('status', LicenseStatus::ACTIVE)
    ->update(['fallback' => true]);

// Delete old events
LicenseEvent::where('created_at', '<', now()->subMonths(6))
    ->delete();

// Reset all usage
LicenseUsage::query()->update(['consumed_units' => 0]);
```

### Relationships in Queries

```php
// Get licenses with activations
$licenses = License::with('activations')->get();

// Get licenses with recent activations
$licenses = License::with([
    'activations' => fn($q) => $q->where('created_at', '>', now()->subDay())
])->get();

// Get licenses with usage and events
$licenses = License::with('usages', 'events')->get();
```

---

## Custom Model Methods

### Useful Helper Methods

```php
// Check activation count
public function hasAvailableActivations(): bool
{
    return $this->activations()->count() < $this->max_activations;
}

// Get remaining activations
public function remainingActivations(): int
{
    return $this->max_activations - $this->activations()->count();
}

// Check if premium
public function isPremium(): bool
{
    return $this->meta['plan'] === 'premium';
}

// Get customer
public function customer()
{
    return $this->belongsTo(Customer::class, 'customer_id', 'id');
}
```

Add these to your custom License model:

```php
// app/Models/License.php
namespace App\Models;

use Akira\LaravelLicense\Models\License as BaseModel;

class License extends BaseModel
{
    public function hasAvailableActivations(): bool
    {
        return $this->activations()->count() < $this->max_activations;
    }

    public function isPremium(): bool
    {
        return $this->meta['plan'] === 'premium';
    }
}
```

---

## Performance Tips

1. **Eager Load Relations**
   ```php
   $licenses = License::with('activations', 'usages', 'events')->get();
   ```

2. **Index Frequently Queried Columns**
   - `licenses.key` - Used for validation
   - `activations.machine_hash` - Used for activation checks
   - `activations.license_id` - Foreign key
   - `events.created_at` - For audit queries

3. **Archive Old Events**
   ```php
   // Delete events older than 6 months
   LicenseEvent::where('created_at', '<', now()->subMonths(6))->delete();
   ```

4. **Use Pagination for Large Result Sets**
   ```php
   $events = $license->events()->paginate(50);
   ```

---

**Previous**: [Configuration](02-configuration.md) | **Next**: [Pipelines](04-pipelines.md)
