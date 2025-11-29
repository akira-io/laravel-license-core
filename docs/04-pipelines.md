# Pipeline Architecture

Deep dive into Laravel License Core's validation pipeline system.

## Overview

License validation happens through **pipelines** - a series of validation stages that process the license request sequentially.

### Two Main Pipelines

1. **Usage Validation Pipeline** - "Can this installation use the software?"
2. **Update Validation Pipeline** - "Can this installation install/update to this version?"

---

## Usage Validation Pipeline

Validates that a license is valid for current usage.

### Stages (In Order)

#### 1. ResolveLicenseStage

**Purpose**: Find the license by key in the database

```php
// Input
$context->key = 'LIC-xxx'

// This stage:
// 1. Queries License model by key
// 2. Sets $context->license
// 3. Throws LicenseNotFoundException if not found
```

**Exceptions**:
- `LicenseNotFoundException` - License key doesn't exist

---

#### 2. StatusCheckStage

**Purpose**: Verify license status is valid

```php
// Checks that license status is:
// - NOT 'revoked' -> throws LicenseRevokedException
// - NOT 'suspended' -> throws LicenseSuspendedException
// - Active, expired, or in grace period is OK
```

**Exceptions**:
- `LicenseRevokedException` - License is revoked
- `LicenseSuspendedException` - License is suspended

---

#### 3. ExpirationUsageStage

**Purpose**: Check if license has expired

```php
// Checks:
// - If expires_at is in past AND not in grace period
// - Throws LicenseExpiredException if expired
// - Lifetime and credits licenses skip (no expiration)
```

**Exceptions**:
- `LicenseExpiredException` - License expired and no grace period

---

#### 4. GracePeriodStage

**Purpose**: Apply grace period logic if license is expired

```php
// Checks:
// - If grace_ends_at is configured
// - If current time is before grace_ends_at
// - If not, license is truly expired
// - Config: config/license.php grace_period section
```

**Configuration**:
```php
'grace_period' => [
    'subscription' => 30,  // 30 days after expiration
    'trial' => 7,          // 7 days after expiration
    'lifetime' => null,    // No grace
    'annual' => null,      // No grace by default
    'credits' => null,     // N/A
],
```

---

#### 5. DomainCheckStage

**Purpose**: Validate domain restrictions

```php
// Checks:
// - If license has allowed_domains list
//   - Validates current domain matches pattern
//   - Throws DomainNotAllowedException if not
// - If license has blocked_domains list
//   - Throws DomainBlockedException if domain matches
// - Pattern types: glob (default), exact, regex
```

**Exceptions**:
- `DomainBlockedException` - Domain is blocked
- `DomainNotAllowedException` - Domain not in allowed list

**Configuration**:
```php
'domain_validation' => [
    'pattern_type' => 'glob',      // 'glob', 'exact', or 'regex'
    'case_sensitive' => false,
],
```

**License Meta**:
```php
'meta' => [
    'allowed_domains' => ['example.com', '*.example.com'],
    'blocked_domains' => ['spam.example.com'],
]
```

---

#### 6. MachineCheckStage

**Purpose**: Check machine/activation limits

```php
// Checks:
// - Count activations for this license
// - If count >= max_activations
//   - Throws ActivationLimitReachedException
// - Credits type licenses skip (no activation required)
```

**Exceptions**:
- `ActivationLimitReachedException` - Too many activations

**Parameters**:
```php
license()->validateUsage(
    key: $key,
    machine: $machineHash,  // Used to identify machine
    activate: true          // Create activation if passes
)
```

---

#### 7. CreditsUsageStage

**Purpose**: Validate sufficient credits available

```php
// Checks:
// - If license type is 'credits'
// - Gets LicenseUsage record
// - Validates consumed_units < limit
// - Other types skip this stage
```

**Exceptions**:
- `UsageNotConfiguredException` - No usage record found
- `InsufficientCreditsException` - Not enough credits

**Parameters**:
```php
license()->validateUsage(
    key: $key,
    machine: $machine,
    amount: 100  // Amount to consume (optional)
)
```

---

#### 8. AbuseHeuristicsStage

**Purpose**: Detect suspicious activation patterns

```php
// Checks:
// - Counts activations in time window (default: 10 minutes)
// - If count > threshold (default: 10)
// - Logs abuse or suspends license
// - Does NOT throw exception (logged separately)
```

**Configuration**:
```php
'abuse_detection' => [
    'enabled' => true,
    'window_minutes' => 10,
    'activation_threshold' => 10,
    'events_to_monitor' => ['activated'],
    'action_on_abuse' => 'log',  // or 'suspend'
],
```

---

## Update Validation Pipeline

Validates that a license covers a specific software version.

### Stages (In Order)

#### 1. ResolveLicenseStage
Find the license (same as usage)

#### 2. StatusCheckStage
Check status is valid (same as usage)

#### 3. ExpirationUsageStage
Check if expired (same as usage)

#### 4. GracePeriodStage
Apply grace period (same as usage)

#### 5. UpdateWindowStage

**Purpose**: Check if software version is covered

```php
// Checks:
// - If license purchase date >= software release date
// - License must have purchased/activated before version was released
// - Throws VersionNotCoveredException if not
```

**Exceptions**:
- `VersionNotCoveredException` - Version not covered by license

**Parameters**:
```php
license()->validateUpdate(
    key: $key,
    releaseDate: $softwareVersion->released_at,
    domain: $domain,
    machine: $machine
)
```

---

## Running Validation

### Via Facade

```php
// Usage validation
try {
    license()->validateUsage(
        key: 'LIC-xxx',
        machine: hash('sha256', gethostname()),
        domain: request()->getHost(),
        activate: true  // Create activation record
    );
    // License is valid!
} catch (\Akira\LaravelLicense\Exceptions\LicenseException $e) {
    // Handle error
}

// Update validation
try {
    license()->validateUpdate(
        key: 'LIC-xxx',
        releaseDate: now(),
        domain: request()->getHost(),
        machine: hash('sha256', gethostname())
    );
    // Can install this version
} catch (\Akira\LaravelLicense\Exceptions\LicenseException $e) {
    // Cannot install
}
```

### Via Actions

```php
use Akira\LaravelLicense\Actions\ValidateUsageAction;
use Akira\LaravelLicense\Actions\ValidateUpdateAction;

$validateUsage = app(ValidateUsageAction::class);
$validateUsage->handle(key: 'LIC-xxx', ...);

$validateUpdate = app(ValidateUpdateAction::class);
$validateUpdate->handle(key: 'LIC-xxx', ...);
```

### Via Pipeline Directly

```php
use Akira\LaravelLicense\Pipelines\LicenseUsageValidationPipeline;
use Akira\LaravelLicense\ValueObjects\LicenseContext;
use Akira\LaravelLicense\ValueObjects\LicenseKey;
use Akira\LaravelLicense\ValueObjects\DomainName;

$pipeline = app(LicenseUsageValidationPipeline::class);

$context = new LicenseContext(
    key: LicenseKey::fromString('LIC-xxx'),
    domain: DomainName::fromUrlOrHost('api.example.com'),
    license: null  // Populated by ResolveLicenseStage
);

$result = $pipeline->validate($context);
```

---

## Custom Stages

### Creating a Custom Stage

Create a custom validation stage:

```php
// app/Pipelines/Stages/CustomValidationStage.php
namespace App\Pipelines\Stages;

use Akira\LaravelLicense\Contracts\LicenseValidatorStage;
use Akira\LaravelLicense\ValueObjects\LicenseContext;

final class CustomValidationStage implements LicenseValidatorStage
{
    public function __invoke(LicenseContext $context): LicenseContext
    {
        // Get the license
        $license = $context->license;

        // Your validation logic
        if ($license->meta['custom_field'] === 'blocked') {
            throw new \Exception('Custom validation failed');
        }

        // Return the context (allows chaining)
        return $context;
    }
}
```

### Using Custom Stages

Add to pipeline configuration:

```php
'pipeline' => [
    'usage' => [
        'resolve_license',
        'status_check',
        'expiration_usage',
        'grace_period',
        'app.custom-stage',      // Register in container
        'domain_check',
        'machine_check',
        'credits_usage',
        'abuse_heuristics',
    ],
],
```

Register in service provider:

```php
// app/Providers/AppServiceProvider.php
public function register(): void
{
    $this->app->bind('app.custom-stage', function () {
        return new \App\Pipelines\Stages\CustomValidationStage();
    });
}
```

Or use full class name:

```php
'pipeline' => [
    'usage' => [
        'resolve_license',
        'status_check',
        \App\Pipelines\Stages\CustomValidationStage::class,
        'domain_check',
        // ...
    ],
],
```

### Custom Stage Example: Feature Validation

```php
namespace App\Pipelines\Stages;

use Akira\LaravelLicense\Contracts\LicenseValidatorStage;
use Akira\LaravelLicense\Exceptions\LicenseException;
use Akira\LaravelLicense\ValueObjects\LicenseContext;

class FeatureValidationStage implements LicenseValidatorStage
{
    public function __construct(private string $requiredFeature)
    {
    }

    public function __invoke(LicenseContext $context): LicenseContext
    {
        $features = $context->license->meta['features'] ?? [];

        if (!in_array($this->requiredFeature, $features)) {
            throw new LicenseException(
                "Feature '{$this->requiredFeature}' not included in license"
            );
        }

        return $context;
    }
}
```

Usage:

```php
$pipeline = app(LicenseUsageValidationPipeline::class);
$stage = new FeatureValidationStage('api_access');

// Inject into pipeline...
```

---

## Customizing Pipeline Order

### Reorder Stages

```php
'pipeline' => [
    'usage' => [
        'resolve_license',
        'status_check',
        'domain_check',      // Check domain early
        'expiration_usage',
        'grace_period',
        'machine_check',
        'credits_usage',
        'abuse_heuristics',
    ],
],
```

### Skip Stages

```php
'pipeline' => [
    'usage' => [
        'resolve_license',
        'status_check',
        'expiration_usage',
        // Skip grace_period
        'domain_check',
        // Skip machine_check for testing
        'credits_usage',
    ],
],
```

### Add Multiple Custom Stages

```php
'pipeline' => [
    'usage' => [
        'resolve_license',
        'status_check',
        'custom.feature-check',
        'custom.rate-limit-check',
        'expiration_usage',
        'grace_period',
        'domain_check',
        'machine_check',
        'custom.custom-audit',
        'credits_usage',
        'abuse_heuristics',
    ],
],
```

---

## Error Handling in Pipelines

### Exception Flow

```php
try {
    license()->validateUsage(key: 'LIC-xxx', ...);
} catch (DomainNotAllowedException $e) {
    // Handle domain-specific error
    return response()->json(['error' => 'Domain not allowed'], 403);
} catch (LicenseExpiredException $e) {
    // Handle expiration
    return response()->json(['error' => 'License expired'], 403);
} catch (ActivationLimitReachedException $e) {
    // Handle activation limit
    return response()->json(['error' => 'Too many activations'], 403);
} catch (\Akira\LaravelLicense\Exceptions\LicenseException $e) {
    // Handle all other license errors
    return response()->json(['error' => $e->getMessage()], 403);
}
```

### Early Exit

Stages stop processing on exception - later stages don't run:

```
Input
  ↓
ResolveLicenseStage ✓
  ↓
StatusCheckStage → Throws LicenseRevokedException ✗
  ↓
(These don't run)
ExpirationUsageStage ✗
GracePeriodStage ✗
DomainCheckStage ✗
...
```

---

## Performance Considerations

### 1. Query Optimization

```php
// Bad: N+1 queries
foreach ($licenses as $license) {
    license()->validateUsage(key: $license->key, ...);
}

// Better: Eager load
$licenses = License::with('activations', 'usages')->get();
```

### 2. Stage Ordering

Put fast checks first:

```php
'pipeline' => [
    'resolve_license',       // Single DB query
    'status_check',          // In-memory check
    'domain_check',          // Pattern matching (fast)
    'machine_check',         // Count query
    'expiration_usage',      // In-memory check
    'grace_period',          // In-memory check
    'credits_usage',         // Query
    'abuse_heuristics',      // Complex query (slow)
],
```

### 3. Cache Pipeline Configuration

```bash
php artisan config:cache
```

---

## Testing Pipelines

### Unit Test Example

```php
use Tests\TestCase;

class LicenseValidationTest extends TestCase
{
    public function test_license_validation_passes()
    {
        $license = License::factory()->create();

        $this->assertTrue(
            license()->validateUsage(
                key: $license->key,
                machine: hash('sha256', 'test'),
                activate: false
            )
        );
    }

    public function test_expired_license_fails()
    {
        $license = License::factory()
            ->expired()
            ->create();

        $this->expectException(LicenseExpiredException::class);

        license()->validateUsage(key: $license->key, ...);
    }
}
```

---

**Previous**: [Models](03-models.md) | **Next**: [Usage Guide](05-usage-guide.md)
