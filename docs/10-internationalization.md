# Internationalization

Multi-language support for error messages and exceptions in Laravel License Core.

## Overview

Laravel License Core provides translations for all error messages and exception texts in multiple languages. Messages are stored in language files and can be easily customized.

### Supported Languages

- **English** (`en`) - Default
- **Portuguese** (`pt_BR`) - Brazilian Portuguese
- **Spanish** (`es`) - Spanish
- **French** (`fr`) - French
- **German** (`de`) - German

---

## File Structure

Translation files are located in:

```
resources/lang/
├── en/
│   ├── exceptions.php
│   ├── messages.php
│   └── validation.php
├── pt_BR/
│   ├── exceptions.php
│   ├── messages.php
│   └── validation.php
├── es/
│   ├── exceptions.php
│   ├── messages.php
│   └── validation.php
├── fr/
│   ├── exceptions.php
│   ├── messages.php
│   └── validation.php
└── de/
    ├── exceptions.php
    ├── messages.php
    └── validation.php
```

---

## Exception Messages

Exception messages are translated from `resources/lang/{locale}/exceptions.php`:

```php
return [
    'license_not_found' => 'License not found',
    'license_expired' => 'License has expired',
    'license_revoked' => 'License has been revoked',
    'license_suspended' => 'License is suspended',
    'activation_limit_reached' => 'Maximum activation limit reached',
    'domain_not_allowed' => 'Domain is not allowed for this license',
    'domain_blocked' => 'Domain is blocked for this license',
    'insufficient_credits' => 'Insufficient credits available',
    'version_not_covered' => 'This version is not covered by your license',
    'usage_not_configured' => 'Usage is not configured for this license',
];
```

### Example Usage

```php
try {
    license()->validateUsage(key: $key, ...)
} catch (LicenseExpiredException $e) {
    // Message is already translated
    echo $e->getMessage();  // Output: "License has expired" (or translated)
}
```

---

## Custom Message Translation

### Override Translation File

Publish the translation files:

```bash
php artisan vendor:publish --tag="laravel-license-core-translations"
```

This creates files in `resources/lang/{locale}/` that you can customize.

### Example: Portuguese Translation

```php
// resources/lang/pt_BR/exceptions.php
return [
    'license_not_found' => 'Licença não encontrada',
    'license_expired' => 'A licença expirou',
    'license_revoked' => 'A licença foi revogada',
    'license_suspended' => 'A licença está suspensa',
    'activation_limit_reached' => 'Limite máximo de ativações atingido',
    'domain_not_allowed' => 'O domínio não é permitido para esta licença',
    'domain_blocked' => 'O domínio está bloqueado para esta licença',
    'insufficient_credits' => 'Créditos insuficientes disponíveis',
    'version_not_covered' => 'Esta versão não é coberta por sua licença',
    'usage_not_configured' => 'O uso não está configurado para esta licença',
];
```

---

## Setting Application Locale

Set the locale in your application:

```php
// config/app.php
'locale' => 'en',

// or at runtime
app()->setLocale('pt_BR');

// or in middleware
if (auth()->check() && auth()->user()->locale) {
    app()->setLocale(auth()->user()->locale);
}
```

### Middleware Example

```php
// app/Http/Middleware/SetLocale.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('Accept-Language') ?? config('app.locale');

        if (in_array($locale, ['en', 'pt_BR', 'es', 'fr', 'de'])) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
```

Register in HTTP kernel:

```php
// app/Http/Kernel.php
protected $middleware = [
    // ...
    \App\Http\Middleware\SetLocale::class,
];
```

---

## Adding New Languages

### 1. Create Language Directory

```bash
mkdir resources/lang/it
```

### 2. Create Translation Files

```php
// resources/lang/it/exceptions.php
return [
    'license_not_found' => 'Licenza non trovata',
    'license_expired' => 'La licenza è scaduta',
    'license_revoked' => 'La licenza è stata revocata',
    'license_suspended' => 'La licenza è sospesa',
    'activation_limit_reached' => 'Limite massimo di attivazioni raggiunto',
    'domain_not_allowed' => 'Il dominio non è consentito per questa licenza',
    'domain_blocked' => 'Il dominio è bloccato per questa licenza',
    'insufficient_credits' => 'Crediti insufficienti disponibili',
    'version_not_covered' => 'Questa versione non è coperta dalla tua licenza',
    'usage_not_configured' => 'L\'utilizzo non è configurato per questa licenza',
];
```

### 3. Register Language

```php
// config/app.php
'supported_locales' => ['en', 'pt_BR', 'es', 'fr', 'de', 'it'],
```

---

## Fallback Behavior

If a translation is missing for a key:

1. Falls back to default locale (usually `en`)
2. If still missing, uses the key name as message

```php
// Example: Missing translation
try {
    license()->validateUsage(...);
} catch (LicenseException $e) {
    // Falls back to: 'license_expired' (the key itself)
}
```

---

## Message Formatting

Some messages can include parameters:

```php
// resources/lang/en/exceptions.php
return [
    'license_expires_in' => 'License expires in :days days',
    'activations_remaining' => 'You have :remaining of :max activations remaining',
];
```

Use in custom exception handling:

```php
$message = trans('license::exceptions.license_expires_in', [
    'days' => $daysRemaining
]);
```

---

## Using Translations in Your Code

### Direct Translation

```php
use Illuminate\Support\Facades\Lang;

// Translate exception message
$message = trans('license::exceptions.license_not_found');

// With parameters
$message = trans('license::exceptions.activations_remaining', [
    'remaining' => 2,
    'max' => 5,
]);
```

### In Controllers

```php
final class LicenseCheckController
{
    public function check(Request $request)
    {
        try {
            license()->validateUsage(
                key: $request->header('X-License-Key'),
                machine: hash('sha256', gethostname()),
                domain: $request->getHost(),
                activate: true
            );

            return response()->json(['valid' => true]);
        } catch (LicenseExpiredException $e) {
            return response()->json([
                'error' => $e->getMessage(),  // Already translated
                'error_code' => 'license_expired'
            ], 403);
        } catch (LicenseException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'error_code' => 'license_error'
            ], 403);
        }
    }
}
```

### In Notifications

```php
use Illuminate\Notifications\Notification;

class LicenseExpiredNotification extends Notification
{
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->error()
            ->subject(trans('license::messages.license_expired'))
            ->line(trans('license::messages.renew_license_soon'))
            ->action(
                trans('license::messages.renew_now'),
                url('/licenses/renew')
            );
    }
}
```

---

## Translation Keys Reference

### Exception Keys

```php
'license::exceptions.license_not_found'
'license::exceptions.license_expired'
'license::exceptions.license_revoked'
'license::exceptions.license_suspended'
'license::exceptions.activation_limit_reached'
'license::exceptions.domain_not_allowed'
'license::exceptions.domain_blocked'
'license::exceptions.insufficient_credits'
'license::exceptions.version_not_covered'
'license::exceptions.usage_not_configured'
```

### Message Keys

Common message translation keys:

```php
'license::messages.license_valid'
'license::messages.license_invalid'
'license::messages.license_active'
'license::messages.license_expired'
'license::messages.renew_license'
'license::messages.activate_license'
'license::messages.deactivate_license'
'license::messages.license_activated'
'license::messages.license_deactivated'
```

---

## Validating Translations

Ensure all translation keys are available:

```php
// Check if translation exists
if (trans()->has('license::exceptions.custom_key')) {
    // Translation exists
}

// Get translation with fallback
$message = trans('license::exceptions.custom_key', [], null, 'License validation failed');
```

---

## Environment-Specific Locales

### Multiple Locales for Same Region

```php
// config/app.php
'locales' => [
    'en' => 'en_US',
    'en_GB' => 'en_GB',
    'pt' => 'pt_PT',
    'pt_BR' => 'pt_BR',
],

// Middleware
app()->setLocale($request->header('Accept-Language') ?? config('app.locale'));
```

---

## Performance Optimization

### Cache Translations

```php
// config/app.php or Artisan command
php artisan config:cache

// Clears translation cache
php artisan cache:clear
```

### Load Only Needed Locales

```php
// Middleware
protected $supportedLocales = ['en', 'pt_BR', 'es'];

public function handle(Request $request, Closure $next)
{
    $locale = $request->header('Accept-Language');

    if (!in_array($locale, $this->supportedLocales)) {
        $locale = config('app.locale');
    }

    app()->setLocale($locale);

    return $next($request);
}
```

---

## Testing Translations

### Test Exception Messages

```php
use Tests\TestCase;

class TranslationTest extends TestCase
{
    public function test_license_not_found_translation(): void
    {
        app()->setLocale('en');

        expect(trans('license::exceptions.license_not_found'))
            ->toBe('License not found');
    }

    public function test_license_expired_translation_pt_br(): void
    {
        app()->setLocale('pt_BR');

        expect(trans('license::exceptions.license_expired'))
            ->toBe('A licença expirou');
    }

    public function test_message_with_parameters(): void
    {
        $message = trans('license::exceptions.activations_remaining', [
            'remaining' => 2,
            'max' => 5,
        ]);

        expect($message)->toContain('2');
        expect($message)->toContain('5');
    }
}
```

---

## Contributing Translations

To contribute a new language:

1. Create language directory: `resources/lang/{locale}/`
2. Copy all translation files from `resources/lang/en/`
3. Translate all strings
4. Test with your language selected
5. Submit pull request

---

## Locale Detection

### Auto-Detect from Request

```php
// Detect from Accept-Language header
public static function detectLocale(Request $request): string
{
    $acceptLanguage = $request->header('Accept-Language', '');

    foreach (explode(',', $acceptLanguage) as $language) {
        $locale = explode(';', trim($language))[0];

        if (in_array($locale, config('license.supported_locales'))) {
            return $locale;
        }
    }

    return config('app.locale');
}
```

### Detect from User Preferences

```php
// In middleware
if (auth()->check() && auth()->user()->locale) {
    app()->setLocale(auth()->user()->locale);
}
```

---

## Best Practices

1. **Always use translations** - Don't hardcode error messages
2. **Group related translations** - Keep messages organized by namespace
3. **Use meaningful keys** - Make translation keys descriptive
4. **Test all locales** - Ensure all translations work in tests
5. **Update translations together** - Keep all locales in sync
6. **Document new keys** - Add comments explaining complex messages
7. **Use fallbacks** - Provide English fallback for all messages

---

**Previous**: [Testing](09-testing.md) | **Documentation Complete**
