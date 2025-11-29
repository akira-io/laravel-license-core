# Installation

This guide will walk you through installing and setting up Laravel License Core in your application.

## Requirements

Before installing, ensure your system meets these requirements:

- PHP 8.4 or higher
- Laravel 12.x or higher
- Composer 2.x
- One of the following databases:
  - MySQL 5.7+
  - PostgreSQL 9.6+
  - SQLite 3.8+
  - MariaDB 10.2+

## Installation Steps

### Step 1: Install via Composer

Install the package using Composer:

```bash
composer require akira/laravel-license-core
```

### Step 2: Publish Migrations

Publish the package migrations to your application:

```bash
php artisan vendor:publish --tag="laravel-license-core-migrations"
```

This will create a migration file in your `database/migrations` directory that sets up all required tables.

### Step 3: Run Migrations

Execute the migrations to create the database tables:

```bash
php artisan migrate
```

This will create the following tables:

- `licenses` - Main license table
- `license_activations` - License activation records
- `license_usages` - Usage tracking for credit-based licenses
- `license_events` - Event log for audit trail

### Step 4: Publish Configuration (Optional)

If you need to customize table names or model classes, publish the configuration file:

```bash
php artisan vendor:publish --tag="laravel-license-core-config"
```

This creates `config/license.php` in your application.

### Step 5: Publish Translations (Optional)

To customize error messages in different languages:

```bash
php artisan vendor:publish --tag="laravel-license-core-translations"
```

This publishes language files to `resources/lang/{locale}/`.

## Verifying Installation

After installation, verify everything is working correctly:

### Check Database Tables

Verify the tables were created:

```bash
php artisan tinker
```

```php
// In tinker
DB::table('licenses')->count(); // Should return 0
DB::table('license_activations')->count(); // Should return 0
```

### Create a Test License

Try creating a license to confirm everything works:

```php
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Enums\LicenseStatus;
use Illuminate\Support\Str;

$license = License::create([
    'key' => 'LIC-' . Str::uuid(),
    'type' => LicenseType::TRIAL,
    'status' => LicenseStatus::ACTIVE,
]);

echo "License created with key: {$license->key}";
```

If this works without errors, your installation is successful.

## Installation in Production

When deploying to production, follow these additional steps:

### 1. Cache Configuration

Cache your configuration files for better performance:

```bash
php artisan config:cache
```

### 2. Optimize Autoloader

Optimize Composer's autoloader:

```bash
composer install --optimize-autoloader --no-dev
```

### 3. Set Application Key

Ensure your application has a secure encryption key:

```bash
php artisan key:generate
```

This is crucial as the package uses Laravel's encryption for metadata.

### 4. Run Migrations Safely

In production, always backup your database before running migrations:

```bash
# Backup your database first
php artisan migrate --force
```

## Upgrading

When upgrading to a new version of the package:

### 1. Update the Package

```bash
composer update akira/laravel-license-core
```

### 2. Publish New Migrations

```bash
php artisan vendor:publish --tag="laravel-license-core-migrations" --force
```

### 3. Run New Migrations

```bash
php artisan migrate
```

### 4. Clear Caches

```bash
php artisan config:clear
php artisan cache:clear
```

## Troubleshooting

### Migration Errors

If you encounter migration errors:

**Table already exists**:
```bash
# Check if tables exist
php artisan db:show

# If needed, rollback
php artisan migrate:rollback

# Then migrate again
php artisan migrate
```

**Foreign key constraint errors**:
- Ensure you're using InnoDB engine for MySQL
- Check that referenced tables exist first

### Composer Issues

**Memory limit exceeded**:
```bash
php -d memory_limit=-1 /usr/local/bin/composer require akira/laravel-license-core
```

**Version conflicts**:
```bash
# Check your Laravel version
php artisan --version

# Update all dependencies
composer update
```

### Database Connection Issues

Verify your database configuration in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## Uninstalling

If you need to remove the package:

### 1. Rollback Migrations

```bash
php artisan migrate:rollback --path=database/migrations/create_laravel_license_table.php
```

### 2. Remove Package

```bash
composer remove akira/laravel-license-core
```

### 3. Clean Up

```bash
# Remove published config
rm config/license.php

# Clear caches
php artisan config:clear
php artisan cache:clear
```

## Next Steps

Now that you have Laravel License Core installed, proceed to:

- [Configuration](02-configuration.md) - Learn about configuration options
- [Models](03-models.md) - Understand the data models
- [Usage Guide](05-usage-guide.md) - Start using the package

---

**Previous**: [Index](00-index.md) | **Next**: [Configuration](02-configuration.md)
