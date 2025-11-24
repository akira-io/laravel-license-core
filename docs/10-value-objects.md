# Value Objects

Value Objects are immutable objects that represent domain concepts in Laravel License. They provide type safety, encapsulation, and business logic for specific values.

## Overview

All value objects in this package are:
- **Readonly**: Properties cannot be modified after instantiation
- **Immutable**: Creating new instances rather than modifying existing ones
- **Type-safe**: Strong typing ensures data integrity
- **Self-contained**: Encapsulate validation and business logic

## Available Value Objects

### 1. LicenseKey

Represents a license key string with validation and formatting capabilities.

#### Usage

```php
use Akira\LaravelLicense\ValueObjects\LicenseKey;

// Create from constructor
$key = new LicenseKey('XXXX-XXXX-XXXX-XXXX');

// Create from static factory
$key = LicenseKey::fromString('XXXX-XXXX-XXXX-XXXX');

// Convert to string
echo $key; // XXXX-XXXX-XXXX-XXXX
echo $key->value; // XXXX-XXXX-XXXX-XXXX
```

#### Properties

- `value` (string): The license key value

#### Methods

- `__construct(string $value)`: Create new instance
- `fromString(string $key): self`: Static factory method
- `__toString(): string`: Convert to string

#### Examples

```php
// Different key formats
$uuid = new LicenseKey('550e8400-e29b-41d4-a716-446655440000');
$custom = new LicenseKey('PROD-2024-ENTERPRISE-001');
$simple = new LicenseKey('ABC123DEF456');

// Use in queries
$license = License::where('key', $key->value)->first();

// Store in database
$license->update(['key' => $key->value]);
```

---

### 2. DomainName

Represents a domain name or hostname with URL parsing capabilities.

#### Usage

```php
use Akira\LaravelLicense\ValueObjects\DomainName;

// Create from constructor
$domain = new DomainName('example.com');

// Create from URL or host
$domain = DomainName::fromUrlOrHost('https://example.com/path');
$domain = DomainName::fromUrlOrHost('sub.example.com');
$domain = DomainName::fromUrlOrHost('192.168.1.1');

// Access host
echo $domain->host; // example.com
```

#### Properties

- `host` (string): The extracted hostname

#### Methods

- `__construct(string $host)`: Create new instance
- `fromUrlOrHost(?string $domain): ?self`: Parse URL or use as-is

#### Parsing Rules

```php
// URL with scheme - extracts host
DomainName::fromUrlOrHost('https://example.com:8080/path');
// Result: example.com

// Plain domain - uses as-is
DomainName::fromUrlOrHost('example.com');
// Result: example.com

// With authentication
DomainName::fromUrlOrHost('https://user:pass@example.com');
// Result: example.com

// Returns null for empty/invalid
DomainName::fromUrlOrHost(null); // null
DomainName::fromUrlOrHost(''); // null
DomainName::fromUrlOrHost('http:///invalid'); // null
```

#### Examples

```php
// Track activation domain
$activation = LicenseActivation::create([
    'license_id' => $license->id,
    'domain' => DomainName::fromUrlOrHost($request->input('domain'))?->host,
]);

// Validate domain
$domain = DomainName::fromUrlOrHost($input);
if ($domain && str_ends_with($domain->host, '.example.com')) {
    // Valid subdomain
}

// Different domain types
$subdomain = DomainName::fromUrlOrHost('api.example.com');
$localhost = DomainName::fromUrlOrHost('localhost');
$ip = DomainName::fromUrlOrHost('192.168.1.1');
$ipv6 = DomainName::fromUrlOrHost('[2001:db8::1]');
```

---

### 3. MachineFingerprint

Represents a hashed machine identifier for device tracking.

#### Usage

```php
use Akira\LaravelLicense\ValueObjects\MachineFingerprint;

// Create from hash
$fingerprint = new MachineFingerprint('abc123...');

// Create from raw identifier (auto-hashes with SHA256)
$fingerprint = MachineFingerprint::fromRaw('machine-unique-id');

// Access hash
echo $fingerprint->hash; // SHA256 hash (64 characters)
```

#### Properties

- `hash` (string): SHA256 hash of the machine identifier

#### Methods

- `__construct(string $hash)`: Create from existing hash
- `fromRaw(?string $v): ?self`: Create from raw value with auto-hashing

#### Hashing

The `fromRaw()` method automatically applies SHA256 hashing:

```php
$raw = 'machine-12345';
$fingerprint = MachineFingerprint::fromRaw($raw);

// Equivalent to:
$fingerprint = new MachineFingerprint(hash('sha256', $raw));
```

#### Examples

```php
// Track device
$machineId = request()->header('X-Machine-ID');
$fingerprint = MachineFingerprint::fromRaw($machineId);

$activation = LicenseActivation::create([
    'license_id' => $license->id,
    'machine_hash' => $fingerprint?->hash,
]);

// Consistent hashing
$fp1 = MachineFingerprint::fromRaw('device-123');
$fp2 = MachineFingerprint::fromRaw('device-123');
$fp1->hash === $fp2->hash; // true

// Different devices
$fp1 = MachineFingerprint::fromRaw('device-123');
$fp2 = MachineFingerprint::fromRaw('device-456');
$fp1->hash !== $fp2->hash; // true

// Handles null/empty
MachineFingerprint::fromRaw(null); // null
MachineFingerprint::fromRaw(''); // null
```

---

### 4. UsageAmount

Represents usage tracking with limits and consumption calculations.

#### Usage

```php
use Akira\LaravelLicense\ValueObjects\UsageAmount;

// Create usage tracker
$usage = new UsageAmount(limit: 1000, used: 250);

// Check remaining
$remaining = $usage->remaining(); // 750

// Check if enough available
if ($usage->hasEnough(100)) {
    // Can consume 100 units
}
```

#### Properties

- `limit` (int): Maximum allowed usage
- `used` (int): Current usage amount

#### Methods

- `__construct(int $limit, int $used)`: Create instance
- `remaining(): int`: Calculate remaining usage (never negative)
- `hasEnough(int $amount): bool`: Check if amount is available

#### Calculation Rules

```php
// Normal case
$usage = new UsageAmount(limit: 1000, used: 250);
$usage->remaining(); // 750
$usage->hasEnough(750); // true
$usage->hasEnough(751); // false

// At limit
$usage = new UsageAmount(limit: 100, used: 100);
$usage->remaining(); // 0
$usage->hasEnough(1); // false

// Over limit (negative protection)
$usage = new UsageAmount(limit: 100, used: 150);
$usage->remaining(); // 0 (not -50)
$usage->hasEnough(1); // false
```

#### Examples

```php
// Track API calls
$license = License::find($id);
$usage = new UsageAmount(
    limit: $license->max_api_calls,
    used: $license->used_api_calls
);

if ($usage->hasEnough(1)) {
    // Process API call
    $license->increment('used_api_calls');
} else {
    throw new Exception('API limit exceeded');
}

// Display quota
return [
    'limit' => $usage->limit,
    'used' => $usage->used,
    'remaining' => $usage->remaining(),
    'percentage' => ($usage->used / $usage->limit) * 100,
];

// Zero limit check
$unlimited = new UsageAmount(limit: 0, used: 0);
$unlimited->hasEnough(0); // true
$unlimited->hasEnough(1); // false
```

---

### 5. LicenseScopes

Represents permission scopes for a license.

#### Usage

```php
use Akira\LaravelLicense\ValueObjects\LicenseScopes;

// Create with scopes
$scopes = new LicenseScopes(['read', 'write', 'admin']);

// Create with null (no scopes)
$scopes = new LicenseScopes(null);

// Check scope
if ($scopes->has('admin')) {
    // User has admin scope
}
```

#### Properties

- `scopes` (?array): Array of scope strings or null

#### Methods

- `__construct(?array $scopes)`: Create instance
- `has(string $scope): bool`: Check if scope exists (strict comparison)

#### Scope Checking

```php
$scopes = new LicenseScopes(['read', 'write', 'delete']);

$scopes->has('read'); // true
$scopes->has('write'); // true
$scopes->has('admin'); // false

// Case-sensitive
$scopes = new LicenseScopes(['Admin']);
$scopes->has('Admin'); // true
$scopes->has('admin'); // false

// Null scopes
$scopes = new LicenseScopes(null);
$scopes->has('anything'); // false

// Empty scopes
$scopes = new LicenseScopes([]);
$scopes->has('anything'); // false
```

#### Examples

```php
// Define license scopes
$license = License::create([
    'key' => $key,
    'scopes' => ['api:read', 'api:write', 'webhooks:manage'],
]);

// Check permissions
$scopes = new LicenseScopes($license->scopes);

if ($scopes->has('api:write')) {
    // Allow API write operations
}

if ($scopes->has('webhooks:manage')) {
    // Allow webhook management
}

// Multiple scope check
$requiredScopes = ['api:read', 'api:write'];
$hasAllScopes = collect($requiredScopes)->every(
    fn($scope) => $scopes->has($scope)
);

// Scope-based features
return [
    'canRead' => $scopes->has('api:read'),
    'canWrite' => $scopes->has('api:write'),
    'canDelete' => $scopes->has('api:delete'),
    'isAdmin' => $scopes->has('admin'),
];
```

---

### 6. LicenseMeta

Represents arbitrary metadata stored with a license.

#### Usage

```php
use Akira\LaravelLicense\ValueObjects\LicenseMeta;

// Create with data
$meta = new LicenseMeta([
    'customer_id' => 123,
    'plan' => 'enterprise',
    'features' => ['sso', 'api', 'support'],
]);

// Create with null
$meta = new LicenseMeta(null);

// Get values
$customerId = $meta->get('customer_id'); // 123
$plan = $meta->get('plan', 'basic'); // enterprise
$missing = $meta->get('missing'); // null
$default = $meta->get('missing', 'default'); // default
```

#### Properties

- `data` (?array): Associative array of metadata or null

#### Methods

- `__construct(?array $data)`: Create instance
- `get(string $key, mixed $default = null): mixed`: Get value with optional default

#### Access Patterns

```php
$meta = new LicenseMeta([
    'customer' => [
        'id' => 123,
        'name' => 'John Doe',
    ],
    'billing' => [
        'plan' => 'pro',
        'amount' => 99.99,
    ],
]);

// Direct access
$customer = $meta->get('customer');
// ['id' => 123, 'name' => 'John Doe']

// With default
$trial = $meta->get('trial_ends_at', now()->addDays(30));

// Null data
$empty = new LicenseMeta(null);
$empty->get('anything'); // null
$empty->get('anything', 'default'); // default
```

#### Examples

```php
// Store customer info
$license = License::create([
    'key' => $key,
    'meta' => [
        'customer_id' => $customer->id,
        'company_name' => $customer->company,
        'contact_email' => $customer->email,
        'purchase_order' => 'PO-2024-001',
    ],
]);

// Access metadata
$meta = new LicenseMeta($license->meta);
$customerId = $meta->get('customer_id');
$company = $meta->get('company_name', 'Unknown');

// Store feature flags
$license->update([
    'meta' => [
        'features' => [
            'sso' => true,
            'api_rate_limit' => 1000,
            'custom_domain' => true,
        ],
    ],
]);

$meta = new LicenseMeta($license->meta);
$features = $meta->get('features', []);

// Conditional features
if ($meta->get('features.sso')) {
    // Enable SSO
}

// Type-safe access
$apiLimit = $meta->get('features.api_rate_limit', 100);
```

---

### 7. LicenseContext

Represents the complete context of a license validation request.

#### Usage

```php
use Akira\LaravelLicense\ValueObjects\{
    LicenseContext,
    LicenseKey,
    DomainName,
    MachineFingerprint
};

// Create context
$context = new LicenseContext(
    key: new LicenseKey('XXX-XXX-XXX'),
    domain: DomainName::fromUrlOrHost('example.com'),
    machine: MachineFingerprint::fromRaw('machine-123'),
    ip: '192.168.1.1',
    ua: 'Mozilla/5.0...'
);

// Access properties
$context->key; // LicenseKey
$context->domain; // DomainName|null
$context->machine; // MachineFingerprint|null
$context->ip; // string|null
$context->ua; // string|null
$context->license; // License|null

// Add license after validation
$newContext = $context->withLicense($license);
```

#### Properties

- `key` (LicenseKey): The license key
- `domain` (?DomainName): Domain name if provided
- `machine` (?MachineFingerprint): Machine fingerprint if provided
- `ip` (?string): IP address if provided
- `ua` (?string): User agent if provided
- `license` (?License): Associated license model

#### Methods

- `__construct(...)`: Create new context
- `withLicense(License $license): self`: Return new instance with license

#### Immutability

```php
$context1 = new LicenseContext(
    key: $key,
    domain: null,
    machine: null,
    ip: null,
    ua: null
);

// withLicense returns NEW instance
$context2 = $context1->withLicense($license);

$context1->license; // null (original unchanged)
$context2->license; // License instance
$context1 !== $context2; // true (different instances)
```

#### Examples

```php
// Build from request
$context = new LicenseContext(
    key: LicenseKey::fromString($request->input('license_key')),
    domain: DomainName::fromUrlOrHost($request->input('domain')),
    machine: MachineFingerprint::fromRaw($request->input('machine_id')),
    ip: $request->ip(),
    ua: $request->userAgent()
);

// Validate and add license
$license = License::where('key', $context->key->value)->first();
if ($license) {
    $context = $context->withLicense($license);
}

// Use in activation
if ($context->license) {
    LicenseActivation::create([
        'license_id' => $context->license->id,
        'domain' => $context->domain?->host,
        'machine_hash' => $context->machine?->hash,
        'ip_address' => $context->ip,
        'user_agent' => $context->ua,
    ]);
}

// Minimal context
$minimalContext = new LicenseContext(
    key: new LicenseKey($key),
    domain: null,
    machine: null,
    ip: null,
    ua: null
);
```

---

### 8. UpdateEntitlement

Represents software update entitlements with expiration checking.

#### Usage

```php
use Akira\LaravelLicense\ValueObjects\UpdateEntitlement;
use Illuminate\Support\Carbon;

// Create with expiration date
$entitlement = new UpdateEntitlement(
    updatesUntil: Carbon::parse('2025-12-31'),
    fallbackMode: false
);

// Create with no expiration (lifetime updates)
$entitlement = new UpdateEntitlement(
    updatesUntil: null,
    fallbackMode: false
);

// Check if can install version
$releaseDate = Carbon::parse('2025-06-15');
if ($entitlement->canInstall($releaseDate)) {
    // Can install this version
}
```

#### Properties

- `updatesUntil` (?Carbon): Update expiration date or null for unlimited
- `fallbackMode` (bool): Fallback mode flag

#### Methods

- `__construct(...)`: Create instance
- `canInstall(Carbon $releaseDate): bool`: Check if version can be installed

#### Installation Rules

```php
// Lifetime updates (null expiration)
$entitlement = new UpdateEntitlement(null, false);
$entitlement->canInstall($anyDate); // always true

// Before expiration
$entitlement = new UpdateEntitlement(
    Carbon::parse('2025-12-31'),
    false
);
$entitlement->canInstall(Carbon::parse('2025-06-15')); // true

// On expiration date
$entitlement->canInstall(Carbon::parse('2025-12-31')); // true

// After expiration
$entitlement->canInstall(Carbon::parse('2026-01-01')); // false
```

#### Examples

```php
// Annual license with 1 year updates
$license = License::create([
    'key' => $key,
    'type' => LicenseType::ANNUAL,
    'expires_at' => now()->addYear(),
]);

$entitlement = new UpdateEntitlement(
    updatesUntil: $license->expires_at,
    fallbackMode: false
);

// Check version compatibility
$version = SoftwareVersion::find($versionId);
if (!$entitlement->canInstall($version->released_at)) {
    return response()->json([
        'error' => 'Update expired',
        'message' => 'Your updates expired on ' . 
                    $entitlement->updatesUntil->format('Y-m-d'),
        'upgrade_url' => '/upgrade',
    ], 403);
}

// Lifetime license
$lifetimeLicense = License::create([
    'key' => $key,
    'type' => LicenseType::LIFETIME,
    'expires_at' => null,
]);

$entitlement = new UpdateEntitlement(
    updatesUntil: null,
    fallbackMode: false
);

$entitlement->canInstall($anyDate); // always true

// Grace period handling
$license = License::find($id);
$gracePeriod = now()->addDays(7);

$entitlement = new UpdateEntitlement(
    updatesUntil: $license->expires_at ?? $gracePeriod,
    fallbackMode: $license->expires_at === null
);
```

---

## Common Patterns

### Creating from Request Data

```php
use Illuminate\Http\Request;

public function activate(Request $request)
{
    $context = new LicenseContext(
        key: LicenseKey::fromString($request->input('key')),
        domain: DomainName::fromUrlOrHost($request->input('domain')),
        machine: MachineFingerprint::fromRaw($request->input('machine_id')),
        ip: $request->ip(),
        ua: $request->userAgent()
    );
    
    // Use context for validation
}
```

### Null Safety

All factory methods handle null/empty values gracefully:

```php
DomainName::fromUrlOrHost(null); // returns null
DomainName::fromUrlOrHost(''); // returns null
MachineFingerprint::fromRaw(null); // returns null
MachineFingerprint::fromRaw(''); // returns null
```

### Immutability Benefits

```php
// Original context unchanged
$context1 = new LicenseContext($key, null, null, null, null);
$context2 = $context1->withLicense($license);

// Can safely pass around
function validateLicense(LicenseContext $context): bool
{
    // Cannot modify original context
    $newContext = $context->withLicense($license);
    return true;
}
```

### Type Safety

```php
// Compile-time type checking
function processUsage(UsageAmount $usage): int
{
    return $usage->remaining(); // Always returns int
}

// IDE autocomplete support
$key = new LicenseKey('XXX');
$key-> // IDE shows: value, __toString, fromString
```

## Testing Value Objects

All value objects are fully tested with 100% coverage. See examples in `tests/ValueObjects/`:

```php
// Example test
it('calculates remaining correctly', function () {
    $usage = new UsageAmount(limit: 1000, used: 250);
    
    expect($usage->remaining())->toBe(750);
});

it('is readonly', function () {
    $key = new LicenseKey('TEST');
    
    expect(fn () => $key->value = 'NEW')
        ->toThrow(Error::class);
});
```

## Best Practices

### 1. Use Value Objects for Domain Concepts

```php
// Good
$key = new LicenseKey($input);
$domain = DomainName::fromUrlOrHost($url);

// Avoid
$key = $input;
$domain = parse_url($url, PHP_URL_HOST) ?? $url;
```

### 2. Leverage Factory Methods

```php
// Good - handles null/empty
$domain = DomainName::fromUrlOrHost($input);

// Avoid - manual null checks
if ($input) {
    $host = parse_url($input, PHP_URL_HOST) ?? $input;
    $domain = new DomainName($host);
}
```

### 3. Use Type Hints

```php
// Good - type-safe
public function activate(LicenseContext $context): void
{
    // $context is guaranteed to be valid
}

// Avoid - any type
public function activate($context): void
{
    // Need to validate $context
}
```

### 4. Embrace Immutability

```php
// Good - create new instance
$context2 = $context1->withLicense($license);

// Avoid - trying to mutate
$context1->license = $license; // Error: readonly property
```

### 5. Handle Null Safely

```php
// Good - null-safe access
$host = $context->domain?->host;

// Avoid - potential null pointer
$host = $context->domain->host; // Error if domain is null
```

## Next Steps

- Learn about [Testing](08-testing.md) value objects
- Review actual implementations in `src/ValueObjects/`
- See usage examples in `tests/ValueObjects/`

---

**Navigation**: [Previous: Testing](08-testing.md) | [Next: API Reference](10-api-reference.md)
