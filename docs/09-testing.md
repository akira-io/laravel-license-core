# Testing

Testing strategies and factory usage for Laravel License Core.

## Overview

Laravel License Core provides comprehensive factories for testing all model types and features. The package uses **Pest** for testing with 100% test coverage.

### Test Files

```
tests/
├── Unit/
│   ├── Pipelines/Stages/
│   │   ├── DomainCheckStageTest.php
│   │   ├── ExpirationUsageStageTest.php
│   │   ├── CreditsUsageStageTest.php
│   │   └── ...
│   ├── ValueObjects/
│   │   ├── LicenseKeyTest.php
│   │   ├── DomainNameTest.php
│   │   └── ...
│   └── Actions/
│       ├── ValidateUsageActionTest.php
│       └── ...
├── Pipelines/
│   └── Stages/
│       └── DomainCheckStageTest.php
└── TestCase.php
```

### Running Tests

```bash
# Run all tests
vendor/bin/pest

# Run specific test file
vendor/bin/pest tests/Unit/Pipelines/Stages/DomainCheckStageTest.php

# Run with coverage
vendor/bin/pest --coverage

# Run only failed tests
vendor/bin/pest --failed

# Watch mode
vendor/bin/pest --watch
```

---

## Factories

### License Factory

Create test licenses of any type:

```php
use Akira\LaravelLicense\Models\License;

// Basic license
$license = License::factory()->create();

// Specific type
$license = License::factory()
    ->type('annual')
    ->create();

// Multiple licenses
$licenses = License::factory()
    ->count(10)
    ->create();

// With metadata
$license = License::factory()
    ->meta([
        'customer_id' => 123,
        'plan' => 'premium',
        'features' => ['api', 'webhook'],
    ])
    ->create();

// Expired license
$license = License::factory()
    ->expired()
    ->create();

// In grace period
$license = License::factory()
    ->inGracePeriod()
    ->create();

// Suspended license
$license = License::factory()
    ->suspended()
    ->create();

// Revoked license
$license = License::factory()
    ->revoked()
    ->create();
```

### LicenseActivation Factory

Create activation records:

```php
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseActivation;

$license = License::factory()->create();

// Basic activation
$activation = LicenseActivation::factory()
    ->for($license)
    ->create();

// Multiple activations
$activations = LicenseActivation::factory()
    ->for($license)
    ->count(5)
    ->create();

// With specific data
$activation = LicenseActivation::factory()
    ->for($license)
    ->state([
        'domain' => 'api.example.com',
        'machine_hash' => hash('sha256', 'test-machine'),
        'ip' => '192.168.1.1',
    ])
    ->create();
```

### LicenseUsage Factory

Create usage records for credit-based licenses:

```php
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseUsage;

$license = License::factory()
    ->type('credits')
    ->create();

// Basic usage
$usage = LicenseUsage::factory()
    ->for($license)
    ->create();

// With specific limits
$usage = LicenseUsage::factory()
    ->for($license)
    ->state([
        'limit' => 10000,
        'consumed_units' => 2500,
    ])
    ->create();

// Depleted usage
$usage = LicenseUsage::factory()
    ->for($license)
    ->state([
        'limit' => 1000,
        'consumed_units' => 1000,
    ])
    ->create();
```

### LicenseEvent Factory

Create audit log events:

```php
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseEvent;
use Akira\LaravelLicense\Enums\LicenseEventType;

$license = License::factory()->create();

// License created event
$event = LicenseEvent::factory()
    ->for($license)
    ->type(LicenseEventType::CREATED)
    ->state([
        'payload' => [
            'created_by' => auth()->id(),
            'plan' => 'premium',
        ],
    ])
    ->create();

// Activation event
$event = LicenseEvent::factory()
    ->for($license)
    ->type(LicenseEventType::ACTIVATED)
    ->create();

// Usage consumed event
$event = LicenseEvent::factory()
    ->for($license)
    ->type(LicenseEventType::USAGE_CONSUMED)
    ->state([
        'payload' => [
            'units_consumed' => 100,
            'remaining' => 9900,
        ],
    ])
    ->create();
```

---

## Testing Common Scenarios

### Test License Validation

```php
use Tests\TestCase;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Exceptions\LicenseExpiredException;

class LicenseValidationTest extends TestCase
{
    public function test_active_license_passes_validation(): void
    {
        $license = License::factory()->create();

        expect(
            license()->validateUsage(
                key: $license->key,
                machine: 'test-hash',
                activate: false
            )
        )->toBeTrue();
    }

    public function test_expired_license_throws_exception(): void
    {
        $license = License::factory()->expired()->create();

        expect(function () {
            license()->validateUsage(
                key: $license->key,
                machine: 'test-hash',
                activate: false
            );
        })->toThrow(LicenseExpiredException::class);
    }

    public function test_revoked_license_throws_exception(): void
    {
        $license = License::factory()->revoked()->create();

        expect(function () {
            license()->validateUsage(
                key: $license->key,
                machine: 'test-hash',
                activate: false
            );
        })->toThrow(LicenseRevokedException::class);
    }
}
```

### Test Activation Limits

```php
use Tests\TestCase;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseActivation;
use Akira\LaravelLicense\Exceptions\ActivationLimitReachedException;

class ActivationLimitTest extends TestCase
{
    public function test_activation_limit_is_enforced(): void
    {
        $license = License::factory()
            ->state(['max_activations' => 2])
            ->create();

        // Create 2 activations
        LicenseActivation::factory()
            ->for($license)
            ->count(2)
            ->create();

        // Third activation should fail
        expect(function () use ($license) {
            license()->validateUsage(
                key: $license->key,
                machine: hash('sha256', 'new-machine'),
                activate: true
            );
        })->toThrow(ActivationLimitReachedException::class);
    }

    public function test_reusing_machine_hash_succeeds(): void
    {
        $license = License::factory()
            ->state(['max_activations' => 1])
            ->create();

        $machineHash = hash('sha256', 'test-machine');

        // First activation
        LicenseActivation::factory()
            ->for($license)
            ->state(['machine_hash' => $machineHash])
            ->create();

        // Reusing same machine should succeed
        expect(
            license()->validateUsage(
                key: $license->key,
                machine: $machineHash,
                activate: true
            )
        )->toBeTrue();
    }
}
```

### Test Domain Validation

```php
use Tests\TestCase;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Exceptions\DomainNotAllowedException;

class DomainValidationTest extends TestCase
{
    public function test_allowed_domain_passes(): void
    {
        $license = License::factory()
            ->meta([
                'allowed_domains' => ['api.example.com'],
            ])
            ->create();

        expect(
            license()->validateUsage(
                key: $license->key,
                machine: 'test-hash',
                domain: 'api.example.com',
                activate: false
            )
        )->toBeTrue();
    }

    public function test_disallowed_domain_throws_exception(): void
    {
        $license = License::factory()
            ->meta([
                'allowed_domains' => ['api.example.com'],
            ])
            ->create();

        expect(function () use ($license) {
            license()->validateUsage(
                key: $license->key,
                machine: 'test-hash',
                domain: 'other.example.com',
                activate: false
            );
        })->toThrow(DomainNotAllowedException::class);
    }

    public function test_wildcard_domain_matching(): void
    {
        config(['license.domain_validation' => [
            'pattern_type' => 'glob',
            'case_sensitive' => false,
        ]]);

        $license = License::factory()
            ->meta([
                'allowed_domains' => ['*.example.com'],
            ])
            ->create();

        expect(
            license()->validateUsage(
                key: $license->key,
                machine: 'test-hash',
                domain: 'api.example.com',
                activate: false
            )
        )->toBeTrue();
    }

    public function test_blocked_domain_throws_exception(): void
    {
        $license = License::factory()
            ->meta([
                'blocked_domains' => ['spam.example.com'],
            ])
            ->create();

        expect(function () use ($license) {
            license()->validateUsage(
                key: $license->key,
                machine: 'test-hash',
                domain: 'spam.example.com',
                activate: false
            );
        })->toThrow(DomainBlockedException::class);
    }
}
```

### Test Credits Consumption

```php
use Tests\TestCase;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseUsage;
use Akira\LaravelLicense\Exceptions\InsufficientCreditsException;

class CreditsConsumptionTest extends TestCase
{
    public function test_consume_credits_reduces_balance(): void
    {
        $license = License::factory()
            ->type('credits')
            ->create();

        LicenseUsage::factory()
            ->for($license)
            ->state([
                'limit' => 1000,
                'consumed_units' => 0,
            ])
            ->create();

        license()->consumeCredits(
            key: $license->key,
            amount: 100
        );

        $usage = $license->usages()->first();
        expect($usage->consumed_units)->toBe(100);
    }

    public function test_insufficient_credits_throws_exception(): void
    {
        $license = License::factory()
            ->type('credits')
            ->create();

        LicenseUsage::factory()
            ->for($license)
            ->state([
                'limit' => 100,
                'consumed_units' => 100,
            ])
            ->create();

        expect(function () use ($license) {
            license()->consumeCredits(
                key: $license->key,
                amount: 1
            );
        })->toThrow(InsufficientCreditsException::class);
    }

    public function test_remaining_credits_calculation(): void
    {
        $license = License::factory()
            ->type('credits')
            ->create();

        $usage = LicenseUsage::factory()
            ->for($license)
            ->state([
                'limit' => 1000,
                'consumed_units' => 300,
            ])
            ->create();

        expect($usage->remaining())->toBe(700);
    }
}
```

### Test Grace Period

```php
use Tests\TestCase;
use Akira\LaravelLicense\Models\License;

class GracePeriodTest extends TestCase
{
    public function test_license_in_grace_period_passes(): void
    {
        $license = License::factory()
            ->inGracePeriod()
            ->create();

        expect(
            license()->validateUsage(
                key: $license->key,
                machine: 'test-hash',
                activate: false
            )
        )->toBeTrue();
    }

    public function test_license_past_grace_period_fails(): void
    {
        $license = License::factory()
            ->type('subscription')
            ->state([
                'expires_at' => now()->subDays(40),
                'grace_ends_at' => now()->subDays(10),
            ])
            ->create();

        expect(function () use ($license) {
            license()->validateUsage(
                key: $license->key,
                machine: 'test-hash',
                activate: false
            );
        })->toThrow(LicenseExpiredException::class);
    }
}
```

---

## Testing Actions

### Test ValidateUsageAction

```php
use Tests\TestCase;
use Akira\LaravelLicense\Actions\ValidateUsageAction;
use Akira\LaravelLicense\Models\License;

class ValidateUsageActionTest extends TestCase
{
    public function test_validates_active_license(): void
    {
        $license = License::factory()->create();
        $action = app(ValidateUsageAction::class);

        $result = $action->handle(
            key: $license->key,
            machine: 'test-hash',
            domain: 'test.com',
            activate: false
        );

        expect($result)->toBeTrue();
    }

    public function test_creates_activation_when_requested(): void
    {
        $license = License::factory()->create();
        $machineHash = hash('sha256', 'test-machine');

        app(ValidateUsageAction::class)->handle(
            key: $license->key,
            machine: $machineHash,
            domain: 'test.com',
            activate: true
        );

        expect(
            $license->activations()
                ->where('machine_hash', $machineHash)
                ->exists()
        )->toBeTrue();
    }
}
```

### Test ActivateLicenseAction

```php
use Tests\TestCase;
use Akira\LaravelLicense\Actions\ActivateLicenseAction;
use Akira\LaravelLicense\Models\License;

class ActivateLicenseActionTest extends TestCase
{
    public function test_creates_activation(): void
    {
        $license = License::factory()->create();
        $action = app(ActivateLicenseAction::class);

        $activation = $action->handle(
            license: $license,
            domain: 'api.example.com',
            machine: hash('sha256', 'test-machine'),
            ip: '192.168.1.1',
            userAgent: 'Mozilla/5.0'
        );

        expect($activation->license_id)->toBe($license->id)
            ->and($activation->domain)->toBe('api.example.com');
    }
}
```

---

## Integration Tests

### Test Complete Workflow

```php
use Tests\TestCase;
use Akira\LaravelLicense\Models\License;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LicenseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_license_lifecycle(): void
    {
        // 1. Create license
        $license = License::factory()
            ->type('annual')
            ->state([
                'max_activations' => 3,
                'meta' => ['plan' => 'professional'],
            ])
            ->create();

        // 2. Validate license
        expect(
            license()->validateUsage(
                key: $license->key,
                machine: hash('sha256', 'machine-1'),
                activate: true
            )
        )->toBeTrue();

        // 3. Check activations
        expect($license->activations()->count())->toBe(1);

        // 4. Add more activations
        license()->validateUsage(
            key: $license->key,
            machine: hash('sha256', 'machine-2'),
            activate: true
        );

        license()->validateUsage(
            key: $license->key,
            machine: hash('sha256', 'machine-3'),
            activate: true
        );

        expect($license->activations()->count())->toBe(3);

        // 5. Try to exceed limit
        expect(function () use ($license) {
            license()->validateUsage(
                key: $license->key,
                machine: hash('sha256', 'machine-4'),
                activate: true
            );
        })->toThrow(ActivationLimitReachedException::class);

        // 6. Deactivate one machine
        $license->activations()->first()->delete();
        expect($license->activations()->count())->toBe(2);

        // 7. Now new activation succeeds
        expect(
            license()->validateUsage(
                key: $license->key,
                machine: hash('sha256', 'machine-4'),
                activate: true
            )
        )->toBeTrue();
    }
}
```

---

## Using TestCase Base Class

The package provides a `TestCase` base class with helpers:

```php
use Tests\TestCase;

abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use CreatesApplication;

    // Setup for all tests
    protected function setUp(): void
    {
        parent::setUp();
        // Additional test setup
    }
}
```

---

## Configuration for Tests

### Use Testing Config

```php
// tests/TestCase.php or specific tests

protected function setUp(): void
{
    parent::setUp();

    // Override config for tests
    config(['license.abuse_detection' => [
        'enabled' => false,  // Disable for testing
    ]]);

    config(['license.grace_period' => [
        'subscription' => 0,  // No grace period in tests
    ]]);
}
```

### Test Database

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class LicenseTest extends TestCase
{
    use RefreshDatabase;  // Rollback migrations after each test

    public function test_something(): void
    {
        // Fresh database for each test
    }
}
```

---

## Coverage

Run tests with coverage:

```bash
vendor/bin/pest --coverage

# Generate HTML coverage report
vendor/bin/pest --coverage --coverage-html=coverage
```

---

**Previous**: [Actions](07-actions.md) | **Next**: [Internationalization](10-internationalization.md)
