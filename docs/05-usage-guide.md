# Usage Guide

Practical examples and common patterns for using Laravel License Core.

## Creating Licenses

### Lifetime License

Never expires, single activation optional:

```php
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Enums\LicenseStatus;

$license = License::create([
    'key' => 'LIC-' . Str::uuid(),
    'type' => LicenseType::LIFETIME,
    'status' => LicenseStatus::ACTIVE,
    'max_activations' => 1,
    'max_seats' => 1,
    'expires_at' => null,  // Never expires
    'meta' => [
        'customer_email' => 'john@example.com',
        'plan' => 'lifetime',
    ],
]);
```

### Annual License

Year-based, requires activation:

```php
$license = License::create([
    'key' => 'LIC-' . Str::uuid(),
    'type' => LicenseType::ANNUAL,
    'status' => LicenseStatus::ACTIVE,
    'max_activations' => 5,
    'max_seats' => 10,
    'expires_at' => now()->addYear(),
    'meta' => [
        'customer_email' => 'john@example.com',
        'plan' => 'professional',
        'purchase_date' => now()->toDateString(),
    ],
]);
```

### Subscription License

Month-based with grace period:

```php
$license = License::create([
    'key' => 'LIC-' . Str::uuid(),
    'type' => LicenseType::SUBSCRIPTION,
    'status' => LicenseStatus::ACTIVE,
    'max_activations' => 3,
    'max_seats' => 5,
    'expires_at' => now()->addMonth(),
    'grace_ends_at' => now()->addMonth()->addDays(30),
    'meta' => [
        'customer_email' => 'john@example.com',
        'plan' => 'starter',
        'billing_cycle' => 'monthly',
    ],
]);
```

### Trial License

Limited trial with grace period:

```php
$license = License::create([
    'key' => 'LIC-' . Str::uuid(),
    'type' => LicenseType::TRIAL,
    'status' => LicenseStatus::ACTIVE,
    'max_activations' => 1,
    'max_seats' => 1,
    'expires_at' => now()->addDays(30),
    'grace_ends_at' => now()->addDays(37),  // 7 days grace
    'meta' => [
        'customer_email' => 'trial@example.com',
        'plan' => 'trial',
        'trial_starts_at' => now(),
    ],
]);
```

### Credits License

Credit-pool based:

```php
$license = License::create([
    'key' => 'LIC-' . Str::uuid(),
    'type' => LicenseType::CREDITS,
    'status' => LicenseStatus::ACTIVE,
    'max_activations' => 0,  // N/A for credits
    'expires_at' => null,     // Never expires
    'meta' => [
        'customer_email' => 'john@example.com',
        'plan' => 'pay-as-you-go',
    ],
]);

// Create usage record
$license->usages()->create([
    'consumed_units' => 0,
    'limit' => 10000,  // 10k credits available
]);
```

---

## License Validation

### Basic Validation

```php
use Akira\LaravelLicense\Exceptions\LicenseException;

try {
    license()->validateUsage(
        key: request()->header('X-License-Key'),
        machine: hash('sha256', gethostname()),
        activate: true  // Create activation record
    );
    // License is valid!
} catch (LicenseException $e) {
    return response()->json(['error' => $e->getMessage()], 403);
}
```

### With Domain Validation

```php
try {
    license()->validateUsage(
        key: 'LIC-xxx',
        machine: hash('sha256', gethostname()),
        domain: request()->getHost(),  // Validate domain
        activate: true
    );
} catch (DomainNotAllowedException) {
    return response()->json(['error' => 'Domain not allowed'], 403);
}
```

### Update Validation

```php
try {
    license()->validateUpdate(
        key: 'LIC-xxx',
        releaseDate: $softwareVersion->released_at,
        domain: request()->getHost(),
        machine: hash('sha256', gethostname())
    );
    // Can install this version
} catch (VersionNotCoveredException) {
    return response()->json(['error' => 'Version not covered'], 403);
}
```

---

## Managing Activations

### Creating an Activation

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

### Check Activation Limits

```php
$currentCount = $license->activations()->count();
$canActivate = $currentCount < $license->max_activations;

if (!$canActivate) {
    return response()->json([
        'error' => 'Max activations reached',
        'current' => $currentCount,
        'max' => $license->max_activations,
    ], 403);
}
```

### Find Existing Activation

```php
$activation = $license->activations()
    ->where('machine_hash', $machineHash)
    ->first();

if ($activation) {
    // Machine already activated
    return $activation;
}
```

### Deactivate

```php
// Delete specific activation
$activation->delete();

// Deactivate all for a license
$license->activations()->delete();

// Deactivate on specific machine
$license->activations()
    ->where('machine_hash', $machineHash)
    ->delete();
```

---

## Tracking Usage & Credits

### Create Usage Record

```php
$usage = $license->usages()->create([
    'consumed_units' => 0,
    'limit' => 10000,
]);
```

### Consume Credits

```php
// Using facade
license()->consumeCredits(
    key: $license->key,
    amount: 100  // Consume 100 units
);

// Manual update
$usage->increment('consumed_units', 100);

// Check remaining
$remaining = $usage->remaining();
```

### Monitor Usage

```php
$usage = $license->usages()->first();

$stats = [
    'total' => $usage->limit,
    'consumed' => $usage->consumed_units,
    'remaining' => $usage->remaining(),
    'percent_used' => ($usage->consumed_units / $usage->limit) * 100,
];

if ($stats['remaining'] < 100) {
    // Send low credit warning
    Mail::send(new LowCreditsWarning($license, $usage));
}
```

### Refund Credits

```php
// Refund 100 credits
$usage->update([
    'consumed_units' => DB::raw('consumed_units - 100'),
]);
```

### Reset Usage

```php
// Reset for new billing period
$usage->update([
    'consumed_units' => 0,
]);
```

---

## Event Logging

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
        'machine_hash' => $machineHash,
    ],
]);

// Credits consumed
LicenseEvent::create([
    'license_id' => $license->id,
    'type' => LicenseEventType::USAGE_CONSUMED,
    'payload' => [
        'units_consumed' => 100,
        'remaining' => 9900,
    ],
]);
```

### Querying Events

```php
// Get all events
$events = $license->events()
    ->orderBy('created_at', 'desc')
    ->get();

// Get specific type
$activations = $license->events()
    ->where('type', LicenseEventType::ACTIVATED)
    ->get();

// Recent events
$recent = $license->events()
    ->where('created_at', '>', now()->subDay())
    ->get();

// Event count by type
$counts = $license->events()
    ->select('type', DB::raw('count(*) as count'))
    ->groupBy('type')
    ->get();
```

---

## Domain Validation

### Configure Domain Restrictions

```php
$license = License::create([
    'key' => 'LIC-xxx',
    'type' => LicenseType::ANNUAL,
    'meta' => [
        'allowed_domains' => [
            'example.com',
            '*.example.com',
        ],
        'blocked_domains' => [
            'spam.example.com',
        ],
    ],
]);
```

### Glob Pattern (Default)

```php
// config/license.php
'domain_validation' => [
    'pattern_type' => 'glob',
    'case_sensitive' => false,
],

// Patterns
'allowed_domains' => [
    'example.com',              // Exact
    '*.example.com',            // All subdomains
    '*.prod.example.com',       // Nested subdomains
    'api-*.example.com',        // Wildcard prefix
],
```

### Exact Pattern

```php
'domain_validation' => [
    'pattern_type' => 'exact',
    'case_sensitive' => false,
],

'allowed_domains' => [
    'api.example.com',
    'admin.example.com',
],
```

### Regex Pattern

```php
'domain_validation' => [
    'pattern_type' => 'regex',
    'case_sensitive' => false,
],

'allowed_domains' => [
    '^(api|admin)\.example\.com$',      // Multiple specific
    '^.*\.prod\.example\.com$',         // Production env
    '^client-\d+\.example\.com$',       // Dynamic clients
],
```

### Validate Domain

```php
try {
    license()->validateUsage(
        key: $license->key,
        domain: request()->getHost(),
        activate: true
    );
} catch (DomainBlockedException) {
    return response()->json(['error' => 'Domain is blocked'], 403);
} catch (DomainNotAllowedException) {
    return response()->json(['error' => 'Domain not allowed'], 403);
}
```

---

## Middleware Implementation

### Create Middleware

```php
// app/Http/Middleware/ValidateLicense.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Akira\LaravelLicense\Exceptions\LicenseException;

class ValidateLicense
{
    public function handle(Request $request, Closure $next)
    {
        $licenseKey = $request->header('X-License-Key');

        if (!$licenseKey) {
            return response()->json(['error' => 'License key required'], 401);
        }

        try {
            license()->validateUsage(
                key: $licenseKey,
                machine: $this->getMachineHash($request),
                domain: $request->getHost(),
                activate: true
            );

            $request->attributes->add(['license_key' => $licenseKey]);
            return $next($request);
        } catch (LicenseException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    private function getMachineHash(Request $request): string
    {
        return hash('sha256', gethostname() . '|' . $request->ip());
    }
}
```

### Register Middleware

```php
// app/Http/Kernel.php
protected $routeMiddleware = [
    'validate.license' => \App\Http\Middleware\ValidateLicense::class,
];
```

### Use on Routes

```php
// routes/api.php
Route::middleware('validate.license')->group(function () {
    Route::get('/resource', ResourceController::class);
    Route::post('/action', ActionController::class);
});
```

---

## Common Patterns

### License Renewal

```php
function renewLicense(License $license, int $months = 12): void
{
    DB::transaction(function () use ($license, $months) {
        $newExpiry = $license->expires_at
            ? $license->expires_at->addMonths($months)
            : now()->addMonths($months);

        $license->update([
            'expires_at' => $newExpiry,
            'grace_ends_at' => $newExpiry->copy()->addDays(30),
            'status' => LicenseStatus::ACTIVE,
        ]);

        LicenseEvent::create([
            'license_id' => $license->id,
            'type' => LicenseEventType::CREATED,  // Use appropriate type
            'payload' => [
                'renewed_at' => now(),
                'new_expiry' => $newExpiry,
            ],
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
            'status' => LicenseStatus::SUSPENDED,
        ]);

        LicenseEvent::create([
            'license_id' => $license->id,
            'type' => LicenseEventType::SUSPENDED,  // Use actual enum
            'payload' => [
                'reason' => $reason,
                'suspended_by' => auth()->id(),
                'suspended_at' => now(),
            ],
        ]);
    });
}
```

### License Upgrade

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
                'upgraded_at' => now(),
            ],
        ]);
    });
}
```

### Check if License is Valid

```php
function isLicenseValid(License $license): bool
{
    if ($license->status !== LicenseStatus::ACTIVE->value) {
        return false;
    }

    if ($license->isExpired() && !$license->inGracePeriod()) {
        return false;
    }

    return true;
}
```

---

## Error Handling Examples

```php
use Akira\LaravelLicense\Exceptions\{
    LicenseException,
    LicenseNotFoundException,
    LicenseExpiredException,
    ActivationLimitReachedException,
    InsufficientCreditsException,
    DomainNotAllowedException,
};

try {
    license()->validateUsage(key: 'LIC-xxx', ...);
} catch (LicenseNotFoundException) {
    Log::warning('Invalid license key provided');
    return response()->json(['error' => 'Invalid key'], 404);
} catch (LicenseExpiredException) {
    return response()->json([
        'error' => 'License expired',
        'action' => 'renew',
    ], 403);
} catch (ActivationLimitReachedException) {
    return response()->json([
        'error' => 'Max activations reached',
        'deactivate_url' => route('licenses.deactivate'),
    ], 403);
} catch (DomainNotAllowedException) {
    return response()->json([
        'error' => 'Domain not allowed',
        'support_url' => 'https://support.example.com',
    ], 403);
} catch (LicenseException $e) {
    Log::error('License validation failed: ' . $e->getMessage());
    return response()->json(['error' => 'Validation failed'], 403);
}
```

---

**Previous**: [Pipelines](04-pipelines.md) | **Next**: [Value Objects](06-value-objects.md)
