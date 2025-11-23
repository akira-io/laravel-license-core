# Documentation

Welcome to the Laravel License documentation. This guide will help you understand and use all features of the package.

## Table of Contents

### Getting Started

1. [Introduction](01-introduction.md)
   - Overview and features
   - Use cases
   - Requirements
   - Package philosophy

2. [Installation](02-installation.md)
   - Installation via Composer
   - Publishing migrations
   - Running migrations
   - Verification steps

3. [Configuration](03-configuration.md)
   - Configuration file structure
   - Customizing table names
   - Using custom models
   - Environment-specific configuration

### Core Concepts

4. [Models](04-models.md)
   - License model
   - LicenseActivation model
   - LicenseUsage model
   - LicenseEvent model
   - Relationships and methods

5. [Usage Guide](05-usage-guide.md)
   - Creating licenses
   - Managing activations
   - Tracking usage
   - Event logging
   - License validation
   - Common patterns

6. [Enums](06-enums.md)
   - LicenseType enum
   - LicenseStatus enum
   - LicenseEventType enum
   - Working with enums

### Testing and Development

7. [Factories](07-factories.md)
   - LicenseFactory
   - LicenseActivationFactory
   - LicenseUsageFactory
   - LicenseEventFactory
   - Testing scenarios
   - Seeding data

8. [Testing](08-testing.md)
   - Running tests
   - Test structure
   - Writing tests
   - Best practices
   - Coverage requirements

9. [Value Objects](09-value-objects.md)
   - LicenseKey
   - DomainName
   - MachineFingerprint
   - UsageAmount
   - LicenseScopes
   - LicenseMeta
   - LicenseContext
   - UpdateEntitlement

## Quick Start

If you're new to Laravel License, follow this path:

1. Start with [Introduction](01-introduction.md) to understand what the package does
2. Follow [Installation](02-installation.md) to set up the package
3. Read [Usage Guide](05-usage-guide.md) for practical examples
4. Explore [Models](04-models.md) and [Enums](06-enums.md) for detailed API information

## Code Examples

### Creating a License

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
]);
```

### Activating a License

```php
use Akira\LaravelLicense\Models\LicenseActivation;

$activation = LicenseActivation::create([
    'license_id' => $license->id,
    'domain' => 'example.com',
    'machine_hash' => hash('sha256', 'unique-machine-id'),
    'ip' => request()->ip(),
]);
```

### Tracking Usage

```php
use Akira\LaravelLicense\Models\LicenseUsage;

$usage = LicenseUsage::create([
    'license_id' => $license->id,
    'consumed_units' => 0,
    'limit' => 1000,
]);

// Consume units
$usage->increment('consumed_units', 100);

// Check remaining
$remaining = $usage->remaining(); // 900
```

## Key Features

- **Type-Safe**: Uses PHP 8.4+ enums for type safety
- **Flexible**: Multiple license types and statuses
- **Auditable**: Complete event logging
- **Testable**: Comprehensive factories included
- **Secure**: Encrypted metadata storage
- **Performant**: Optimized queries and relationships

## Package Information

- **Version**: 1.0.0
- **License**: MIT
- **PHP Version**: 8.4+
- **Laravel Version**: 12.x or higher
- **Test Coverage**: 100%
- **Total Tests**: 213
- **Total Assertions**: 338

## Support

For issues, questions, or contributions:

- GitHub Issues: Report bugs and request features
- Pull Requests: Contributions are welcome
- Documentation: This guide and inline code documentation

## Contributing

When contributing to documentation:

1. Follow the existing structure and style
2. Include code examples for new features
3. Update the index in this README
4. Ensure all internal links work
5. Use clear, concise language

## Navigation Tips

- Each document includes navigation links at the bottom
- Use the index above to jump to specific topics
- Code examples are provided throughout
- Internal links use relative paths

## Additional Resources

- [CHANGELOG.md](../CHANGELOG.md) - Version history
- [README.md](../README.md) - Package overview
- [Tests](../tests/) - Test examples
- [Source Code](../src/) - Package source

---

Happy licensing!
