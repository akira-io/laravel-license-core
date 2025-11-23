# Configuration

This guide covers all configuration options available in Laravel License.

## Configuration File

The configuration file is located at `config/license.php`. If you haven't published it yet:

```bash
php artisan vendor:publish --tag="laravel-license-config"
```

## Default Configuration

Here's the default configuration structure:

```php
<?php

return [
    'tables' => [
        'licenses' => 'licenses',
        'activations' => 'license_activations',
        'usages' => 'license_usages',
        'events' => 'license_events',
    ],

    'models' => [
        'license' => \Akira\LaravelLicense\Models\License::class,
        'activation' => \Akira\LaravelLicense\Models\LicenseActivation::class,
        'usage' => \Akira\LaravelLicense\Models\LicenseUsage::class,
        'event' => \Akira\LaravelLicense\Models\LicenseEvent::class,
    ],
];
```

## Table Names Configuration

### Customizing Table Names

You can customize the table names used by the package:

```php
'tables' => [
    'licenses' => 'my_licenses',
    'activations' => 'my_activations',
    'usages' => 'my_usages',
    'events' => 'my_events',
],
```

After changing table names, you'll need to:

1. Update or republish migrations
2. Run migrations with the new table names

### Table Name Prefixes

If you use a table prefix in your application:

```php
// In config/database.php
'prefix' => 'app_',

// Tables will be: app_licenses, app_license_activations, etc.
```

The package respects Laravel's table prefix configuration automatically.

## Model Configuration

### Using Custom Models

You can extend the package models and use your custom implementations:

```php
// app/Models/CustomLicense.php
namespace App\Models;

use Akira\LaravelLicense\Models\License as BaseLicense;

class CustomLicense extends BaseLicense
{
    // Add your custom methods
    public function isEnterprise(): bool
    {
        return $this->meta['plan'] === 'enterprise';
    }
}
```

Update configuration:

```php
'models' => [
    'license' => \App\Models\CustomLicense::class,
    // ...
],
```

### Model Resolution

The package uses `ConfigManager` to resolve model classes:

```php
use Akira\LaravelLicense\Support\ConfigManager;

$configManager = app(ConfigManager::class);
$modelClass = $configManager->getModelClass('license');
```

## Accessing Configuration

### Using ConfigManager

The recommended way to access configuration:

```php
use Akira\LaravelLicense\Support\ConfigManager;

$config = app(ConfigManager::class);

// Get table names
$licensesTable = $config->getLicenseTable();
$activationsTable = $config->getActivationsTable();
$usagesTable = $config->getUsagesTable();
$eventsTable = $config->getEventsTable();

// Get model classes
$licenseModel = $config->getModelClass('license');
$activationModel = $config->getModelClass('activation');

// Get custom config values
$value = $config->get('custom.key', 'default');
```

### Direct Config Access

You can also use Laravel's config helper:

```php
$licensesTable = config('license.tables.licenses');
$licenseModel = config('license.models.license');
```

## Environment-Specific Configuration

### Development Configuration

For development, you might want different settings:

```php
// config/license.php

return [
    'tables' => [
        'licenses' => env('LICENSE_TABLE', 'licenses'),
        // ...
    ],
];
```

In `.env`:

```env
LICENSE_TABLE=dev_licenses
```

### Testing Configuration

In your `phpunit.xml` or test setup:

```php
// tests/TestCase.php
protected function setUp(): void
{
    parent::setUp();
    
    config(['license.tables.licenses' => 'test_licenses']);
}
```

## Advanced Configuration

### Database Connection

Use a specific database connection for licenses:

```php
// In your custom model
namespace App\Models;

use Akira\LaravelLicense\Models\License as BaseLicense;

class CustomLicense extends BaseLicense
{
    protected $connection = 'licensing';
}
```

Configure the connection in `config/database.php`:

```php
'connections' => [
    'licensing' => [
        'driver' => 'mysql',
        'host' => env('LICENSE_DB_HOST', '127.0.0.1'),
        'database' => env('LICENSE_DB_DATABASE', 'licenses'),
        // ...
    ],
],
```

### Multiple Configurations

If you need multiple licensing configurations:

```php
// config/license.php
return [
    'default' => [
        'tables' => [
            'licenses' => 'licenses',
            // ...
        ],
    ],
    
    'enterprise' => [
        'tables' => [
            'licenses' => 'enterprise_licenses',
            // ...
        ],
    ],
];
```

### Caching Configuration

After changing configuration, always clear caches:

```bash
php artisan config:clear
php artisan cache:clear
```

In production, cache the configuration:

```bash
php artisan config:cache
```

## Configuration Best Practices

### 1. Use Environment Variables

For sensitive or environment-specific settings:

```php
return [
    'encryption_key' => env('LICENSE_ENCRYPTION_KEY'),
    'api_endpoint' => env('LICENSE_API_ENDPOINT'),
];
```

### 2. Document Custom Configuration

If you add custom configuration options, document them:

```php
return [
    // Custom: Maximum activations per license
    // Default: 5
    'max_activations' => env('LICENSE_MAX_ACTIVATIONS', 5),
];
```

### 3. Validate Configuration

Add validation in a service provider:

```php
public function boot()
{
    $this->validateConfiguration();
}

private function validateConfiguration()
{
    $tables = config('license.tables');
    
    foreach ($tables as $key => $table) {
        if (empty($table)) {
            throw new \RuntimeException("Table name for '{$key}' is not configured");
        }
    }
}
```

### 4. Version Configuration

Keep track of configuration changes:

```php
return [
    'version' => '1.0.0',
    
    // Your configuration...
];
```

## Configuration Reference

### Available Configuration Keys

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `tables.licenses` | string | `licenses` | Licenses table name |
| `tables.activations` | string | `license_activations` | Activations table name |
| `tables.usages` | string | `license_usages` | Usages table name |
| `tables.events` | string | `license_events` | Events table name |
| `models.license` | string | `License::class` | License model class |
| `models.activation` | string | `LicenseActivation::class` | Activation model class |
| `models.usage` | string | `LicenseUsage::class` | Usage model class |
| `models.event` | string | `LicenseEvent::class` | Event model class |

## Troubleshooting Configuration

### Configuration Not Loading

Clear and recache:

```bash
php artisan config:clear
php artisan config:cache
```

### Table Name Issues

Verify table names:

```bash
php artisan tinker
```

```php
use Akira\LaravelLicense\Support\ConfigManager;

$config = app(ConfigManager::class);
echo $config->getLicenseTable();
```

### Model Resolution Errors

Check if model class exists and is autoloadable:

```bash
composer dump-autoload
```

## Next Steps

Now that you understand configuration, learn about:

- [Models](04-models.md) - Understand the data models
- [Usage Guide](05-usage-guide.md) - Start using the package
- [Factories](07-factories.md) - Use factories for testing

---

**Navigation**: [Previous: Installation](02-installation.md) | [Next: Models](04-models.md)
