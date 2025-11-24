# Internationalization

Laravel License comes with built-in internationalization support for multiple languages.

## Available Languages

- English (en)
- Portuguese Brazil (pt_BR)

## Publishing Translations

To customize translations, publish the language files:

```bash
php artisan vendor:publish --tag="laravel-license-translations"
```

This will copy translation files to `resources/lang/vendor/laravel-license/`.

## Available Translation Keys

### Exception Messages

All exceptions use translation keys from `license.exceptions`:

```php
// English
'activation_limit_reached' => 'Activation limit reached for this license.'
'domain_not_allowed' => 'Domain :domain is not allowed for this license.'
'domain_blocked' => 'Domain :domain is blocked for this license.'
'version_not_covered' => 'Version :version is not covered by this license.'
'insufficient_credits' => 'Insufficient credits. Required: :required, Available: :available'
'license_not_found' => 'License not found.'
'license_not_loaded' => 'License must be loaded before performing this operation.'
'license_expired' => 'License has expired on :date.'
'license_revoked' => 'License has been revoked.'
'license_suspended' => 'License is currently suspended.'
'usage_not_configured' => 'Usage tracking is not configured for this license.'
```

### Status Labels

```php
'status.active' => 'Active'
'status.expired' => 'Expired'
'status.suspended' => 'Suspended'
'status.revoked' => 'Revoked'
```

### Type Labels

```php
'type.perpetual' => 'Perpetual'
'type.subscription' => 'Subscription'
'type.trial' => 'Trial'
```

### Event Labels

```php
'events.activated' => 'License activated'
'events.deactivated' => 'License deactivated'
'events.suspended' => 'License suspended'
'events.resumed' => 'License resumed'
'events.revoked' => 'License revoked'
'events.renewed' => 'License renewed'
'events.upgraded' => 'License upgraded'
'events.downgraded' => 'License downgraded'
```

## Using Translations in Your Application

You can use the translation keys in your application:

```php
// Get translated status
$status = __('laravel-license::license.status.active');

// Get translated type
$type = __('laravel-license::license.type.subscription');

// Get translated event
$event = __('laravel-license::license.events.activated');
```

## Adding New Languages

To add support for a new language:

1. Create a new directory in `resources/lang/vendor/laravel-license/`
2. Copy the `en/license.php` file to your new language directory
3. Translate all the strings

Example for Spanish:

```bash
mkdir -p resources/lang/vendor/laravel-license/es
cp resources/lang/vendor/laravel-license/en/license.php \
   resources/lang/vendor/laravel-license/es/license.php
```

Then edit the Spanish file with your translations.

## Exception Messages with Parameters

Some exception messages include dynamic parameters:

```php
// Domain not allowed
__('laravel-license::license.exceptions.domain_not_allowed', ['domain' => 'example.com']);

// Domain blocked
__('laravel-license::license.exceptions.domain_blocked', ['domain' => 'example.com']);

// Version not covered
__('laravel-license::license.exceptions.version_not_covered', ['version' => '2.0.0']);

// Insufficient credits
__('laravel-license::license.exceptions.insufficient_credits', [
    'required' => 100,
    'available' => 50
]);

// License expired
__('laravel-license::license.exceptions.license_expired', ['date' => '2024-12-31']);
```

## Setting Application Locale

The package respects Laravel's application locale setting:

```php
// Set locale in config/app.php
'locale' => 'pt_BR',

// Or change at runtime
app()->setLocale('pt_BR');
```

## Fallback Behavior

If a translation is not found in the current locale, Laravel will automatically fall back to the fallback locale defined in `config/app.php`.

---

**Previous:** [Events](08-events.md) | **Next:** [Index](00-index.md)
