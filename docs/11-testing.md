# Testing

Laravel License comes with a comprehensive test suite covering 100% of the codebase with 213 tests and 338 assertions.

## Overview

The package uses [Pest PHP](https://pestphp.com/) as the testing framework, providing a clean and expressive syntax for writing tests.

### Test Statistics

- **Total Tests**: 213
- **Total Assertions**: 338
- **Code Coverage**: 100%
- **Test Duration**: ~8.8 seconds

## Running Tests

### Basic Commands

```bash
# Run all tests
composer test

# Run with coverage report
composer test:coverage

# Run using Pest directly
./vendor/bin/pest

# Run with detailed coverage
./vendor/bin/pest --coverage
```

### Running Specific Tests

```bash
# Run single test file
./vendor/bin/pest tests/Models/LicenseTest.php

# Run tests in directory
./vendor/bin/pest tests/ValueObjects/

# Run tests matching pattern
./vendor/bin/pest --filter="can create license"

# Run specific test group
./vendor/bin/pest --group=models
```

### Coverage Options

```bash
# Show coverage in terminal
./vendor/bin/pest --coverage

# Generate HTML coverage report
./vendor/bin/pest --coverage-html=coverage

# Enforce minimum coverage
./vendor/bin/pest --coverage --min=100

# Coverage for specific directory
./vendor/bin/pest tests/Models/ --coverage
```

## Test Structure

### Directory Organization

```
tests/
├── Commands/
│   └── LaravelLicenseCommandTest.php
├── Enums/
│   ├── LicenseEventTypeTest.php
│   ├── LicenseStatusTest.php
│   └── LicenseTypeTest.php
├── Facades/
│   └── LaravelLicenseTest.php
├── Models/
│   ├── LicenseTest.php
│   ├── LicenseActivationTest.php
│   ├── LicenseEventTest.php
│   └── LicenseUsageTest.php
├── Support/
│   └── ConfigManagerTest.php
├── ValueObjects/
│   ├── DomainNameTest.php
│   ├── LicenseContextTest.php
│   ├── LicenseKeyTest.php
│   ├── LicenseMetaTest.php
│   ├── LicenseScopesTest.php
│   ├── MachineFingerprintTest.php
│   ├── UpdateEntitlementTest.php
│   └── UsageAmountTest.php
├── ArchTest.php
├── FactoriesTest.php
├── LaravelLicenseTest.php
├── Pest.php
└── TestCase.php
```

## Test Categories

### 1. Model Tests (58 tests)

Tests for Eloquent models covering CRUD operations, relationships, scopes, and business logic.

```php
// Example: tests/Models/LicenseTest.php
it('can create a license', function () {
    $license = License::factory()->create();
    
    assertDatabaseHas('licenses', [
        'id' => $license->id,
        'status' => 'active',
    ]);
});
```

**Coverage**:
- License: 35+ tests
- LicenseActivation: 8 tests
- LicenseEvent: 8 tests
- LicenseUsage: 7 tests

### 2. Value Object Tests (66 tests)

Tests for immutable value objects ensuring type safety and business rules.

```php
// Example: tests/ValueObjects/LicenseKeyTest.php
it('can create license key with value', function () {
    $key = new LicenseKey('TEST-KEY-123');
    
    expect($key->value)->toBe('TEST-KEY-123');
});
```

**Coverage**:
- DomainName: 19 tests
- LicenseKey: 7 tests
- MachineFingerprint: 8 tests
- UsageAmount: 8 tests
- LicenseScopes: 7 tests
- LicenseMeta: 8 tests
- LicenseContext: 3 tests
- UpdateEntitlement: 6 tests

### 3. Enum Tests (20 tests)

Tests for PHP enums validating all cases and helper methods.

```php
// Example: tests/Enums/LicenseTypeTest.php
it('has all license types', function () {
    $types = LicenseType::cases();
    
    expect($types)->toHaveCount(5);
});
```

**Coverage**:
- LicenseType: 8 tests
- LicenseStatus: 7 tests
- LicenseEventType: 5 tests

### 4. Factory Tests (45 tests)

Tests for database factories ensuring they generate valid test data.

```php
// Example: tests/FactoriesTest.php
it('can create license with factory states', function () {
    $license = License::factory()->active()->annual()->create();
    
    expect($license->status)->toBe('active')
        ->and($license->type)->toBe('annual');
});
```

### 5. Command Tests (10 tests)

Tests for Artisan commands.

```php
// Example: tests/Commands/LaravelLicenseCommandTest.php
it('displays package info', function () {
    $this->artisan('license')
        ->expectsOutput('Laravel License')
        ->assertExitCode(0);
});
```

### 6. Architecture Tests

Tests ensuring code quality and architectural rules.

```php
// Example: tests/ArchTest.php
it('will not use debugging functions', function () {
    expect(['dd', 'dump', 'ray'])
        ->not->toBeUsed();
});
```

## Writing Tests

### Basic Test Structure

```php
<?php

declare(strict_types=1);

use Akira\LaravelLicense\Models\License;

it('can perform action', function () {
    // Arrange
    $license = License::factory()->create();
    
    // Act
    $result = $license->someMethod();
    
    // Assert
    expect($result)->toBeTrue();
});
```

### Using Factories

```php
it('creates license with relationships', function () {
    $license = License::factory()
        ->has(LicenseActivation::factory()->count(3), 'activations')
        ->create();
    
    expect($license->activations)->toHaveCount(3);
});
```

### Database Assertions

```php
it('stores data correctly', function () {
    $license = License::factory()->create([
        'key' => 'TEST-KEY',
    ]);
    
    assertDatabaseHas('licenses', [
        'key' => 'TEST-KEY',
    ]);
});
```

### Testing Exceptions

```php
it('throws exception on invalid data', function () {
    expect(fn () => License::create([]))
        ->toThrow(ValidationException::class);
});
```

### Testing Readonly Properties

```php
it('is readonly', function () {
    $key = new LicenseKey('TEST');
    
    expect(fn () => $key->value = 'NEW')
        ->toThrow(Error::class);
});
```

## Best Practices

### 1. Use Descriptive Test Names

```php
// Good
it('can create annual license with grace period')

// Bad
it('test license creation')
```

### 2. Follow AAA Pattern

Always structure tests with Arrange, Act, Assert:

```php
it('calculates remaining usage correctly', function () {
    // Arrange
    $usage = new UsageAmount(limit: 1000, used: 250);
    
    // Act
    $remaining = $usage->remaining();
    
    // Assert
    expect($remaining)->toBe(750);
});
```

### 3. Test Edge Cases

```php
it('handles zero remaining usage', function () {
    $usage = new UsageAmount(limit: 100, used: 100);
    
    expect($usage->remaining())->toBe(0);
});

it('handles over limit usage', function () {
    $usage = new UsageAmount(limit: 100, used: 150);
    
    expect($usage->remaining())->toBe(0);
});
```

### 4. Use Factories

```php
// Good
$license = License::factory()->active()->create();

// Avoid
$license = new License([
    'key' => 'test',
    'status' => 'active',
    'type' => 'annual',
    // ... many more fields
]);
```

### 5. Test One Thing Per Test

```php
// Good
it('validates license key format', function () {
    // Test only key validation
});

it('validates license expiration date', function () {
    // Test only expiration
});

// Bad
it('validates license', function () {
    // Tests multiple validations
});
```

## Continuous Integration

### GitHub Actions Example

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.4
          extensions: dom, curl, libxml, mbstring, zip
          coverage: xdebug
      
      - name: Install Dependencies
        run: composer install
      
      - name: Run Tests
        run: composer test:coverage
      
      - name: Upload Coverage
        uses: codecov/codecov-action@v3
```

## Test Data

### Using Faker

```php
it('creates license with fake data', function () {
    $license = License::factory()->create([
        'key' => fake()->uuid(),
        'meta' => [
            'customer' => fake()->name(),
            'email' => fake()->email(),
        ],
    ]);
    
    expect($license->key)->toBeString();
});
```

### Custom Test Data

```php
it('handles specific scenarios', function () {
    $testCases = [
        ['limit' => 100, 'used' => 50, 'expected' => 50],
        ['limit' => 100, 'used' => 100, 'expected' => 0],
        ['limit' => 100, 'used' => 150, 'expected' => 0],
    ];
    
    foreach ($testCases as $case) {
        $usage = new UsageAmount($case['limit'], $case['used']);
        expect($usage->remaining())->toBe($case['expected']);
    }
});
```

## Debugging Tests

### Show Test Output

```bash
# Verbose output
./vendor/bin/pest -v

# Very verbose output
./vendor/bin/pest -vv

# Debug mode
./vendor/bin/pest -vvv
```

### Dump and Die in Tests

```php
it('debugs value', function () {
    $license = License::factory()->create();
    
    dump($license->toArray()); // Show value
    // dd($license); // Dump and die
    
    expect($license)->toBeInstanceOf(License::class);
});
```

### Ray Debugging

```php
it('uses ray for debugging', function () {
    $license = License::factory()->create();
    
    ray($license); // Send to Ray app
    
    expect($license)->not->toBeNull();
});
```

## Performance

### Test Optimization

```php
// Use transactions (automatic with RefreshDatabase)
uses(RefreshDatabase::class);

// Create minimal data
$license = License::factory()->make(); // Don't persist

// Batch operations
License::factory()->count(10)->create();
```

### Parallel Testing

```bash
# Run tests in parallel (requires paratest)
./vendor/bin/paratest

# Specify number of processes
./vendor/bin/paratest --processes=4
```

## Coverage Requirements

The package maintains 100% code coverage. All new code must include tests.

### Verify Coverage

```bash
# Ensure 100% coverage
./vendor/bin/pest --coverage --min=100

# Generate HTML report
./vendor/bin/pest --coverage-html=coverage

# Open report
open coverage/index.html
```

### Coverage by Component

All components have 100% coverage:

- Commands: 100%
- Enums: 100%
- Facades: 100%
- Models: 100%
- Support: 100%
- Value Objects: 100%

## Troubleshooting

### Common Issues

**Issue**: Tests fail with database errors

```bash
# Solution: Run migrations
php artisan migrate --env=testing
```

**Issue**: Coverage not generating

```bash
# Solution: Install xdebug
pecl install xdebug
```

**Issue**: Slow tests

```bash
# Solution: Use in-memory SQLite
# In phpunit.xml or pest.php:
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

## Next Steps

- Learn about [Value Objects](09-value-objects.md) for immutable domain objects
- Review actual test files in the `tests/` directory for examples
- Explore test examples in `tests/ValueObjects/`

---

**Navigation**: [Previous: Factories](07-factories.md) | [Next: Value Objects](09-value-objects.md)
