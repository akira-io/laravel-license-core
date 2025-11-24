# Exceptions

Laravel License provides a comprehensive set of custom exceptions for handling license-related errors with clarity and precision.

## Exception Hierarchy

All package exceptions extend from a base `LicenseException` class:

```
RuntimeException
    └── LicenseException (base)
        ├── ActivationLimitReachedException
        ├── DomainNotAllowedException
        ├── DomainBlockedException
        ├── VersionNotCoveredException
        ├── InsufficientCreditsException
        ├── LicenseNotFoundException
        ├── LicenseNotLoadedException
        ├── LicenseExpiredException
        ├── LicenseRevokedException
        ├── LicenseSuspendedException
        └── UsageNotConfiguredException
```

## Available Exceptions

### ActivationLimitReachedException

Thrown when attempting to activate a license that has reached its maximum number of activations.

```php
use Akira\LaravelLicense\Exceptions\ActivationLimitReachedException;

throw ActivationLimitReachedException::create();
```

### DomainNotAllowedException

Thrown when attempting to activate a license on a domain that is not in the allowed list.

```php
use Akira\LaravelLicense\Exceptions\DomainNotAllowedException;

throw DomainNotAllowedException::forDomain('example.com');
```

### DomainBlockedException

Thrown when attempting to activate a license on a blocked domain.

```php
use Akira\LaravelLicense\Exceptions\DomainBlockedException;

throw DomainBlockedException::forDomain('blocked.com');
```

### VersionNotCoveredException

Thrown when the license does not cover the requested version.

```php
use Akira\LaravelLicense\Exceptions\VersionNotCoveredException;

throw VersionNotCoveredException::forVersion('2.0.0');
```

### InsufficientCreditsException

Thrown when there are not enough credits available for an operation.

```php
use Akira\LaravelLicense\Exceptions\InsufficientCreditsException;

throw InsufficientCreditsException::create(
    required: 100,
    available: 50
);
```

### LicenseNotFoundException

Thrown when a license cannot be found in the database.

```php
use Akira\LaravelLicense\Exceptions\LicenseNotFoundException;

throw LicenseNotFoundException::create();
```

### LicenseNotLoadedException

Thrown when attempting to perform operations on a license that hasn't been loaded.

```php
use Akira\LaravelLicense\Exceptions\LicenseNotLoadedException;

throw LicenseNotLoadedException::create();
```

### LicenseExpiredException

Thrown when a license has expired.

```php
use Akira\LaravelLicense\Exceptions\LicenseExpiredException;

throw LicenseExpiredException::create('2024-12-31');
```

### LicenseRevokedException

Thrown when attempting to use a revoked license.

```php
use Akira\LaravelLicense\Exceptions\LicenseRevokedException;

throw LicenseRevokedException::create();
```

### LicenseSuspendedException

Thrown when attempting to use a suspended license.

```php
use Akira\LaravelLicense\Exceptions\LicenseSuspendedException;

throw LicenseSuspendedException::create();
```

### UsageNotConfiguredException

Thrown when usage tracking is not configured for a license.

```php
use Akira\LaravelLicense\Exceptions\UsageNotConfiguredException;

throw UsageNotConfiguredException::create();
```

## Handling Exceptions

### Try-Catch Blocks

```php
use Akira\LaravelLicense\Exceptions\LicenseExpiredException;
use Akira\LaravelLicense\Exceptions\LicenseSuspendedException;
use Akira\LaravelLicense\Exceptions\LicenseException;

try {
    $license->activate('example.com');
} catch (LicenseExpiredException $e) {
    // Handle expired license
    return response()->json(['error' => 'License expired'], 403);
} catch (LicenseSuspendedException $e) {
    // Handle suspended license
    return response()->json(['error' => 'License suspended'], 403);
} catch (LicenseException $e) {
    // Handle any other license exception
    return response()->json(['error' => $e->getMessage()], 400);
}
```

### Global Exception Handler

You can register a global handler in `app/Exceptions/Handler.php`:

```php
use Akira\LaravelLicense\Exceptions\LicenseException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->renderable(function (LicenseException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'type' => class_basename($e),
                ], 400);
            }

            return back()->withErrors(['license' => $e->getMessage()]);
        });
    }
}
```

### API Error Responses

```php
use Akira\LaravelLicense\Exceptions\LicenseException;

try {
    $result = $licenseService->validateAndActivate($key, $domain);
    return response()->json($result);
} catch (LicenseException $e) {
    return response()->json([
        'success' => false,
        'error' => [
            'message' => $e->getMessage(),
            'type' => class_basename($e),
            'code' => $e->getCode(),
        ],
    ], 400);
}
```

## Internationalization

All exception messages support internationalization. See [Internationalization](09-internationalization.md) for details.

```php
// Exception messages are automatically translated
app()->setLocale('pt_BR');

try {
    throw ActivationLimitReachedException::create();
} catch (ActivationLimitReachedException $e) {
    echo $e->getMessage(); // "Limite de ativacoes atingido para esta licenca."
}
```

## Best Practices

### 1. Catch Specific Exceptions First

Always catch more specific exceptions before the base exception:

```php
try {
    // ...
} catch (LicenseExpiredException $e) {
    // Specific handling
} catch (LicenseException $e) {
    // General handling
}
```

### 2. Log Exceptions

Always log exceptions for debugging:

```php
use Illuminate\Support\Facades\Log;

try {
    $license->activate($domain);
} catch (LicenseException $e) {
    Log::warning('License activation failed', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'license_key' => $license->key,
        'domain' => $domain,
    ]);
    
    throw $e;
}
```

### 3. Provide User-Friendly Messages

Don't expose raw exception messages to end users:

```php
try {
    $license->activate($domain);
} catch (LicenseExpiredException $e) {
    return response()->json([
        'message' => 'Your license has expired. Please renew to continue.',
        'action' => 'renew',
    ], 403);
} catch (LicenseSuspendedException $e) {
    return response()->json([
        'message' => 'Your license is suspended. Contact support.',
        'action' => 'contact_support',
    ], 403);
}
```

### 4. Use Exception Context

Add context to exceptions for better debugging:

```php
try {
    $license->activate($domain);
} catch (LicenseException $e) {
    $e->context([
        'user_id' => auth()->id(),
        'license_key' => $license->key,
        'domain' => $domain,
        'ip' => request()->ip(),
    ]);
    
    report($e);
}
```

## Testing Exceptions

```php
use Akira\LaravelLicense\Exceptions\LicenseExpiredException;

test('throws exception when license is expired', function () {
    $license = License::factory()->expired()->create();
    
    expect(fn() => $license->activate('example.com'))
        ->toThrow(LicenseExpiredException::class);
});

test('exception message is translated', function () {
    app()->setLocale('pt_BR');
    
    $exception = LicenseExpiredException::create();
    
    expect($exception->getMessage())
        ->toContain('expirou');
});
```

---

**Previous:** [Factories](07-factories.md) | **Next:** [Internationalization](09-internationalization.md)
