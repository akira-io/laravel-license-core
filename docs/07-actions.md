# Actions

Business logic operations in Laravel License Core implemented as Action classes.

## Overview

All business logic operations follow the **Action Pattern**: single-purpose classes with a `handle()` method that encapsulate domain operations and can be used from anywhere in your application (controllers, jobs, commands, other actions).

### Core Actions

| Action | Purpose | Inputs |
|--------|---------|--------|
| **ValidateUsageAction** | Validate license for usage | key, machine, domain, amount, activate |
| **ValidateUpdateAction** | Validate license for updates | key, releaseDate, domain, machine |
| **ActivateLicenseAction** | Create activation record | license, domain, machine |
| **ConsumeCreditsAction** | Deduct credits from pool | key, amount |
| **RotateLicenseKeyAction** | Generate new license key | license |
| **SuspendLicenseAction** | Suspend a license | license, reason |
| **RevokeLicenseAction** | Revoke a license | license, reason |

---

## ValidateUsageAction

Validates that a license is valid for current usage.

### Handle Method

```php
use Akira\LaravelLicense\Actions\ValidateUsageAction;

$action = app(ValidateUsageAction::class);

$result = $action->handle(
    key: 'LIC-xxx',
    machine: hash('sha256', gethostname()),
    domain: request()->getHost(),
    amount: null,              // Optional: for credits
    activate: true             // Create activation
);

// Result is true or throws exception
```

### Usage in Controllers

```php
use Akira\LaravelLicense\Actions\ValidateUsageAction;

final class LicenseCheckController
{
    public function __construct(
        private ValidateUsageAction $validateUsage
    ) {}

    public function check(Request $request)
    {
        try {
            $this->validateUsage->handle(
                key: $request->header('X-License-Key'),
                machine: hash('sha256', gethostname()),
                domain: $request->getHost(),
                activate: true
            );

            return response()->json(['valid' => true]);
        } catch (LicenseException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }
}
```

### Usage in Jobs

```php
use Akira\LaravelLicense\Actions\ValidateUsageAction;

final class ProcessLicenseCheckJob
{
    public function __construct(
        private ValidateUsageAction $validateUsage
    ) {}

    public function handle(): void
    {
        try {
            $this->validateUsage->handle(
                key: $this->licenseKey,
                machine: $this->machineHash,
                domain: $this->domain,
                activate: false
            );

            // License is valid
        } catch (LicenseException $e) {
            Log::warning('License check failed: ' . $e->getMessage());
        }
    }
}
```

---

## ValidateUpdateAction

Validates that a license covers a specific software version.

### Handle Method

```php
use Akira\LaravelLicense\Actions\ValidateUpdateAction;

$action = app(ValidateUpdateAction::class);

$result = $action->handle(
    key: 'LIC-xxx',
    releaseDate: $version->released_at,
    domain: request()->getHost(),
    machine: hash('sha256', gethostname())
);

// Result is true or throws VersionNotCoveredException
```

### Version Check Example

```php
use Akira\LaravelLicense\Actions\ValidateUpdateAction;

final class UpdateCheckController
{
    public function __construct(
        private ValidateUpdateAction $validateUpdate
    ) {}

    public function checkVersion(Request $request, SoftwareVersion $version)
    {
        try {
            $this->validateUpdate->handle(
                key: $request->header('X-License-Key'),
                releaseDate: $version->released_at,
                domain: $request->getHost(),
                machine: hash('sha256', gethostname())
            );

            return response()->json([
                'canUpdate' => true,
                'version' => $version->number
            ]);
        } catch (VersionNotCoveredException) {
            return response()->json([
                'canUpdate' => false,
                'message' => 'License does not cover this version'
            ], 403);
        }
    }
}
```

---

## ActivateLicenseAction

Creates a machine activation record for a license.

### Handle Method

```php
use Akira\LaravelLicense\Actions\ActivateLicenseAction;
use Akira\LaravelLicense\Models\License;

$action = app(ActivateLicenseAction::class);

$activation = $action->handle(
    license: $license,
    domain: 'api.example.com',
    machine: hash('sha256', gethostname()),
    ip: request()->ip(),
    userAgent: request()->userAgent()
);
```

### Complete Example

```php
use Akira\LaravelLicense\Actions\ActivateLicenseAction;
use Akira\LaravelLicense\Models\License;
use Illuminate\Support\Facades\DB;

final class ActivateDeviceAction
{
    public function __construct(
        private ActivateLicenseAction $activate
    ) {}

    public function handle(string $licenseKey): array
    {
        return DB::transaction(function () use ($licenseKey) {
            $license = License::where('key', $licenseKey)->firstOrFail();

            // Check activation limit
            if ($license->activations()->count() >= $license->max_activations) {
                throw new ActivationLimitReachedException();
            }

            // Create activation
            $activation = $this->activate->handle(
                license: $license,
                domain: request()->getHost(),
                machine: hash('sha256', gethostname()),
                ip: request()->ip(),
                userAgent: request()->userAgent()
            );

            return [
                'activated' => true,
                'activation_id' => $activation->id,
                'domain' => $activation->domain
            ];
        });
    }
}
```

---

## ConsumeCreditsAction

Deducts credits from a credit-based license pool.

### Handle Method

```php
use Akira\LaravelLicense\Actions\ConsumeCreditsAction;

$action = app(ConsumeCreditsAction::class);

$result = $action->handle(
    key: 'LIC-xxx',
    amount: 100  // Credits to consume
);

// Returns: true on success, throws exception if insufficient
```

### API Endpoint Example

```php
use Akira\LaravelLicense\Actions\ConsumeCreditsAction;

final class ApiRequestController
{
    public function __construct(
        private ConsumeCreditsAction $consumeCredits
    ) {}

    public function makeRequest(Request $request)
    {
        $licenseKey = $request->header('X-License-Key');
        $creditCost = 100;  // Cost per request

        try {
            // Check and consume credits
            $this->consumeCredits->handle(
                key: $licenseKey,
                amount: $creditCost
            );

            // Process request
            return response()->json(['status' => 'processed']);
        } catch (InsufficientCreditsException) {
            return response()->json([
                'error' => 'Insufficient credits',
                'required' => $creditCost,
                'action' => 'purchase_credits'
            ], 402);
        }
    }
}
```

### Checking Remaining Credits

```php
use Akira\LaravelLicense\Models\License;

$license = License::where('key', $licenseKey)->firstOrFail();
$usage = $license->usages()->first();

$remaining = $usage->remaining();
$percent = ($usage->consumed_units / $usage->limit) * 100;

if ($remaining < 1000) {
    // Send low credit warning
    Mail::send(new LowCreditsWarning($license));
}
```

---

## RotateLicenseKeyAction

Generates a new license key while maintaining the same license record.

### Handle Method

```php
use Akira\LaravelLicense\Actions\RotateLicenseKeyAction;
use Akira\LaravelLicense\Models\License;

$action = app(RotateLicenseKeyAction::class);

$oldKey = $license->key;
$newKey = $action->handle(license: $license);

// License now has new key: $newKey
```

### Example Usage

```php
use Akira\LaravelLicense\Actions\RotateLicenseKeyAction;
use Akira\LaravelLicense\Models\License;
use Illuminate\Support\Facades\DB;

final class RotateLicenseController
{
    public function __construct(
        private RotateLicenseKeyAction $rotateKey
    ) {}

    public function rotate(License $license)
    {
        $oldKey = $license->key;

        $newKey = DB::transaction(function () use ($license, $oldKey) {
            $newKey = $this->rotateKey->handle(license: $license);

            // Log rotation
            LicenseEvent::create([
                'license_id' => $license->id,
                'type' => LicenseEventType::ROTATED,
                'payload' => [
                    'old_key' => $oldKey,
                    'new_key' => $newKey,
                    'rotated_at' => now()->toIso8601String(),
                    'reason' => 'Key compromise',
                ]
            ]);

            return $newKey;
        });

        return response()->json([
            'old_key' => $oldKey,
            'new_key' => $newKey,
            'message' => 'License key rotated successfully'
        ]);
    }
}
```

---

## SuspendLicenseAction

Suspends a license temporarily (can be reactivated).

### Handle Method

```php
use Akira\LaravelLicense\Actions\SuspendLicenseAction;

$action = app(SuspendLicenseAction::class);

$action->handle(
    license: $license,
    reason: 'Payment failed'
);
```

### Complete Example

```php
use Akira\LaravelLicense\Actions\SuspendLicenseAction;
use Akira\LaravelLicense\Models\License;

final class SuspendLicenseController
{
    public function __construct(
        private SuspendLicenseAction $suspend
    ) {}

    public function suspend(License $license, Request $request)
    {
        $this->suspend->handle(
            license: $license,
            reason: $request->input('reason')
        );

        // Notify customer
        Mail::send(new LicenseSuspendedNotification($license));

        return response()->json([
            'status' => 'suspended',
            'license_id' => $license->id
        ]);
    }
}
```

### Resume After Suspension

```php
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Enums\LicenseStatus;

$license->update([
    'status' => LicenseStatus::ACTIVE
]);

LicenseEvent::create([
    'license_id' => $license->id,
    'type' => LicenseEventType::ACTIVATED,
    'payload' => [
        'resumed_at' => now()->toIso8601String(),
        'reason' => 'Payment received',
    ]
]);
```

---

## RevokeLicenseAction

Permanently revokes a license (cannot be reactivated without manual intervention).

### Handle Method

```php
use Akira\LaravelLicense\Actions\RevokeLicenseAction;

$action = app(RevokeLicenseAction::class);

$action->handle(
    license: $license,
    reason: 'License misuse detected'
);
```

### Example

```php
use Akira\LaravelLicense\Actions\RevokeLicenseAction;
use Akira\LaravelLicense\Models\License;

final class RevokeLicenseController
{
    public function __construct(
        private RevokeLicenseAction $revoke
    ) {}

    public function revoke(License $license, Request $request)
    {
        $this->revoke->handle(
            license: $license,
            reason: $request->input('reason')
        );

        // Notify all users
        Notification::send($license->users,
            new LicenseRevokedNotification($license)
        );

        return response()->json([
            'status' => 'revoked',
            'message' => 'License has been revoked'
        ]);
    }
}
```

---

## Dependency Injection in Actions

### Constructor Injection

```php
use Akira\LaravelLicense\Pipelines\LicenseUsageValidationPipeline;
use Akira\LaravelLicense\Models\License;

final readonly class MyCustomAction
{
    public function __construct(
        private LicenseUsageValidationPipeline $pipeline,
        private License $license
    ) {}

    public function handle(string $licenseKey): bool
    {
        // Use injected dependencies
        return $this->pipeline->validate(...);
    }
}
```

### Using in Service Providers

```php
// app/Providers/AppServiceProvider.php
public function register(): void
{
    $this->app->bind(MyCustomAction::class, function ($app) {
        return new MyCustomAction(
            $app->make(LicenseUsageValidationPipeline::class),
            $app->make(License::class)
        );
    });
}
```

---

## Error Handling in Actions

### Try-Catch Pattern

```php
use Akira\LaravelLicense\Actions\ValidateUsageAction;
use Akira\LaravelLicense\Exceptions\{
    LicenseException,
    LicenseNotFoundException,
    LicenseExpiredException,
    ActivationLimitReachedException
};

final class SafeValidateAction
{
    public function __construct(
        private ValidateUsageAction $validate
    ) {}

    public function handle(string $key): array
    {
        try {
            $this->validate->handle(
                key: $key,
                machine: hash('sha256', gethostname()),
                domain: request()->getHost(),
                activate: true
            );

            return ['valid' => true];
        } catch (LicenseNotFoundException) {
            return ['valid' => false, 'error' => 'License not found'];
        } catch (LicenseExpiredException) {
            return ['valid' => false, 'error' => 'License expired'];
        } catch (ActivationLimitReachedException) {
            return ['valid' => false, 'error' => 'Activation limit reached'];
        } catch (LicenseException $e) {
            return ['valid' => false, 'error' => $e->getMessage()];
        }
    }
}
```

---

## Chaining Actions

### Multiple Operations in Transaction

```php
use Akira\LaravelLicense\Actions\{
    ValidateUsageAction,
    ActivateLicenseAction,
    ConsumeCreditsAction
};
use Illuminate\Support\Facades\DB;

final class CompleteOnboardingAction
{
    public function __construct(
        private ValidateUsageAction $validateUsage,
        private ActivateLicenseAction $activate,
        private ConsumeCreditsAction $consumeCredits
    ) {}

    public function handle(string $licenseKey, int $setupCost): array
    {
        return DB::transaction(function () use ($licenseKey, $setupCost) {
            // Step 1: Validate license
            $this->validateUsage->handle(
                key: $licenseKey,
                machine: hash('sha256', gethostname()),
                domain: request()->getHost(),
                activate: false
            );

            $license = License::where('key', $licenseKey)->firstOrFail();

            // Step 2: Activate machine
            $activation = $this->activate->handle(
                license: $license,
                domain: request()->getHost(),
                machine: hash('sha256', gethostname()),
                ip: request()->ip(),
                userAgent: request()->userAgent()
            );

            // Step 3: Consume setup credits
            $this->consumeCredits->handle(
                key: $licenseKey,
                amount: $setupCost
            );

            return [
                'onboarded' => true,
                'activation_id' => $activation->id
            ];
        });
    }
}
```

---

## Testing Actions

### Using in Tests

```php
use Tests\TestCase;
use Akira\LaravelLicense\Actions\ValidateUsageAction;
use Akira\LaravelLicense\Models\License;

class ValidateLicenseTest extends TestCase
{
    public function test_validate_active_license(): void
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
}
```

---

**Previous**: [Exceptions](08-exceptions.md) | **Next**: [Testing](09-testing.md)
