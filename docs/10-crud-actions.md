# CRUD Actions

This package provides dedicated actions for creating and updating licenses using Value Objects for type safety and consistency.

## Creating Licenses

### Using CreateLicenseAction

The `CreateLicenseAction` provides a clean way to create licenses using the `LicenseData` value object:

```php
use Akira\LaravelLicense\Actions\CreateLicenseAction;
use Akira\LaravelLicense\ValueObjects\LicenseData;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Enums\LicenseStatus;

// Resolve the action from the container
$action = app(CreateLicenseAction::class);

// Create license data
$data = new LicenseData(
    key: 'XXXX-XXXX-XXXX-XXXX',
    type: LicenseType::ANNUAL,
    status: LicenseStatus::ACTIVE,
    maxActivations: 5,
    maxSeats: 10,
    fallback: false,
    scopes: ['feature:advanced', 'feature:api'],
    meta: [
        'client_name' => 'Acme Corp',
        'purchase_order' => 'PO-12345',
    ],
    expiresAt: now()->addYear(),
    graceEndsAt: now()->addYear()->addDays(7),
);

// Create the license
$license = $action->handle($data);
```

### Auto-generating License Keys

You can also let the system generate a license key automatically:

```php
$action = app(CreateLicenseAction::class);

$data = new LicenseData(
    key: '', // Will be auto-generated
    type: LicenseType::LIFETIME,
    status: LicenseStatus::ACTIVE,
    maxActivations: 1,
    maxSeats: 1,
    fallback: false,
    scopes: null,
    meta: null,
    expiresAt: null,
    graceEndsAt: null,
);

// Use handleWithAutoKey to generate a key automatically
$license = $action->handleWithAutoKey($data);
```

## Updating Licenses

### Using UpdateLicenseAction

The `UpdateLicenseAction` allows you to update existing licenses:

```php
use Akira\LaravelLicense\Actions\UpdateLicenseAction;
use Akira\LaravelLicense\Models\License;

$license = License::where('key', 'XXXX-XXXX-XXXX-XXXX')->first();

$action = app(UpdateLicenseAction::class);

$data = new LicenseData(
    key: $license->key,
    type: LicenseType::from($license->type),
    status: LicenseStatus::SUSPENDED, // Change status
    maxActivations: 10, // Increase activations
    maxSeats: 20, // Increase seats
    fallback: $license->fallback,
    scopes: ['feature:basic'], // Update scopes
    meta: $license->meta,
    expiresAt: now()->addYear(), // Extend expiration
    graceEndsAt: now()->addYear()->addDays(30),
);

$updatedLicense = $action->handle($license, $data);
```

## LicenseData Value Object

The `LicenseData` value object provides type safety and validation for license data.

### Properties

- `key` (string): The license key
- `type` (LicenseType): The license type enum
- `status` (LicenseStatus): The license status enum
- `maxActivations` (int): Maximum number of activations allowed
- `maxSeats` (int): Maximum number of seats/users allowed
- `fallback` (bool): Whether fallback mode is enabled
- `scopes` (array|null): License scopes/features
- `meta` (array|null): Encrypted metadata
- `expiresAt` (CarbonInterface|null): Expiration date
- `graceEndsAt` (CarbonInterface|null): Grace period end date

### Converting to Array

```php
$data = new LicenseData(/* ... */);
$array = $data->toArray();

// Returns:
// [
//     'key' => 'XXXX-XXXX-XXXX-XXXX',
//     'type' => 'annual',
//     'status' => 'active',
//     'max_activations' => 5,
//     'max_seats' => 10,
//     'fallback' => false,
//     'scopes' => ['feature:advanced'],
//     'meta' => ['client' => 'Test Corp'],
//     'expires_at' => Carbon instance,
//     'grace_ends_at' => Carbon instance,
// ]
```

### Creating from Array

```php
$array = [
    'key' => 'XXXX-XXXX-XXXX-XXXX',
    'type' => 'annual', // or LicenseType::ANNUAL
    'status' => 'active', // or LicenseStatus::ACTIVE
    'max_activations' => 5,
    'max_seats' => 10,
    'fallback' => false,
    'scopes' => ['feature:advanced'],
    'meta' => ['client' => 'Test Corp'],
    'expires_at' => now()->addYear(),
    'grace_ends_at' => now()->addYear()->addDays(7),
];

$data = LicenseData::fromArray($array);
```

## Using the Facade

All CRUD operations are also available through the `License` facade for convenience:

```php
use Akira\LaravelLicense\Facades\License;
use Akira\LaravelLicense\ValueObjects\LicenseData;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Enums\LicenseStatus;

// Create a license
$data = new LicenseData(
    key: 'XXXX-XXXX-XXXX-XXXX',
    type: LicenseType::ANNUAL,
    status: LicenseStatus::ACTIVE,
    maxActivations: 5,
    maxSeats: 10,
    fallback: false,
    scopes: ['feature:advanced'],
    meta: ['client' => 'Acme Corp'],
    expiresAt: now()->addYear(),
    graceEndsAt: null,
);

$license = License::create($data);

// Create with auto-generated key
$license = License::createWithAutoKey($data);

// Update existing license
$updated = License::update($license, $data);

// Update by key (returns null if not found)
$updated = License::updateByKey('XXXX-XXXX-XXXX-XXXX', $data);
```

## Example: Simple Usage

Here's the simplest way to use the CRUD operations with the facade:

```php
use Akira\LaravelLicense\Facades\License;
use Akira\LaravelLicense\ValueObjects\LicenseData;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Enums\LicenseStatus;

// Quick create with auto-generated key
$license = License::createWithAutoKey(
    new LicenseData(
        key: '',
        type: LicenseType::ANNUAL,
        status: LicenseStatus::ACTIVE,
        maxActivations: 5,
        maxSeats: 10,
        fallback: false,
        scopes: ['feature:premium'],
        meta: ['client' => 'Acme Corp'],
        expiresAt: now()->addYear(),
        graceEndsAt: null,
    )
);

// Quick update by key
$updated = License::updateByKey(
    $license->key,
    new LicenseData(
        key: $license->key,
        type: LicenseType::from($license->type),
        status: LicenseStatus::SUSPENDED, // Suspend the license
        maxActivations: $license->max_activations,
        maxSeats: $license->max_seats,
        fallback: $license->fallback,
        scopes: $license->scopes?->getArrayCopy(),
        meta: $license->meta,
        expiresAt: $license->expires_at,
        graceEndsAt: $license->grace_ends_at,
    )
);
```

## Example: API Controller

Here's a practical example of using these actions in a Laravel controller:

```php
<?php

namespace App\Http\Controllers\Api;

use Akira\LaravelLicense\Actions\CreateLicenseAction;
use Akira\LaravelLicense\Actions\UpdateLicenseAction;
use Akira\LaravelLicense\Enums\LicenseStatus;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\ValueObjects\LicenseData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function __construct(
        private readonly CreateLicenseAction $createAction,
        private readonly UpdateLicenseAction $updateAction,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'max_activations' => 'required|integer',
            'max_seats' => 'required|integer',
            'scopes' => 'nullable|array',
            'meta' => 'nullable|array',
            'expires_at' => 'nullable|date',
        ]);

        $data = new LicenseData(
            key: '',
            type: LicenseType::from($validated['type']),
            status: LicenseStatus::ACTIVE,
            maxActivations: $validated['max_activations'],
            maxSeats: $validated['max_seats'],
            fallback: false,
            scopes: $validated['scopes'] ?? null,
            meta: $validated['meta'] ?? null,
            expiresAt: isset($validated['expires_at']) 
                ? carbon($validated['expires_at']) 
                : null,
            graceEndsAt: null,
        );

        $license = $this->createAction->handleWithAutoKey($data);

        return response()->json($license, 201);
    }

    public function update(Request $request, License $license): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'sometimes|string',
            'status' => 'sometimes|string',
            'max_activations' => 'sometimes|integer',
            'max_seats' => 'sometimes|integer',
            'scopes' => 'nullable|array',
            'meta' => 'nullable|array',
            'expires_at' => 'nullable|date',
        ]);

        $data = new LicenseData(
            key: $license->key,
            type: isset($validated['type']) 
                ? LicenseType::from($validated['type']) 
                : LicenseType::from($license->type),
            status: isset($validated['status']) 
                ? LicenseStatus::from($validated['status']) 
                : LicenseStatus::from($license->status),
            maxActivations: $validated['max_activations'] ?? $license->max_activations,
            maxSeats: $validated['max_seats'] ?? $license->max_seats,
            fallback: $license->fallback,
            scopes: $validated['scopes'] ?? $license->scopes?->getArrayCopy(),
            meta: $validated['meta'] ?? $license->meta,
            expiresAt: isset($validated['expires_at']) 
                ? carbon($validated['expires_at']) 
                : $license->expires_at,
            graceEndsAt: $license->grace_ends_at,
        );

        $updatedLicense = $this->updateAction->handle($license, $data);

        return response()->json($updatedLicense);
    }
}
```

### Using Facade in Controller

You can also use the facade directly for simpler code:

```php
<?php

namespace App\Http\Controllers\Api;

use Akira\LaravelLicense\Facades\License;
use Akira\LaravelLicense\Enums\LicenseStatus;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\ValueObjects\LicenseData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'max_activations' => 'required|integer',
            'max_seats' => 'required|integer',
            'scopes' => 'nullable|array',
            'meta' => 'nullable|array',
            'expires_at' => 'nullable|date',
        ]);

        $license = License::createWithAutoKey(
            new LicenseData(
                key: '',
                type: LicenseType::from($validated['type']),
                status: LicenseStatus::ACTIVE,
                maxActivations: $validated['max_activations'],
                maxSeats: $validated['max_seats'],
                fallback: false,
                scopes: $validated['scopes'] ?? null,
                meta: $validated['meta'] ?? null,
                expiresAt: isset($validated['expires_at']) 
                    ? carbon($validated['expires_at']) 
                    : null,
                graceEndsAt: null,
            )
        );

        return response()->json($license, 201);
    }

    public function update(Request $request, string $key): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|string',
            'max_activations' => 'sometimes|integer',
            'max_seats' => 'sometimes|integer',
        ]);

        $license = \Akira\LaravelLicense\Models\License::where('key', $key)->firstOrFail();

        $updated = License::updateByKey(
            $key,
            new LicenseData(
                key: $license->key,
                type: LicenseType::from($license->type),
                status: isset($validated['status']) 
                    ? LicenseStatus::from($validated['status']) 
                    : LicenseStatus::from($license->status),
                maxActivations: $validated['max_activations'] ?? $license->max_activations,
                maxSeats: $validated['max_seats'] ?? $license->max_seats,
                fallback: $license->fallback,
                scopes: $license->scopes?->getArrayCopy(),
                meta: $license->meta,
                expiresAt: $license->expires_at,
                graceEndsAt: $license->grace_ends_at,
            )
        );

        return response()->json($updated);
    }
}
```

## Benefits

### Type Safety

- All data is validated at runtime and compile-time
- Enums ensure only valid types and statuses are used
- Value objects prevent invalid data from being passed around

### Consistency

- All license creation/updates go through the same code path
- Business logic is centralized in actions
- Easy to test and maintain

### Testability

- Actions can be easily mocked in tests
- Value objects make test data creation simple
- Clear separation of concerns

### Flexibility

- Easy to add new validation rules
- Simple to extend with custom logic
- Can be used in API, CLI, queues, etc.

## Related Documentation

- [Models](03-models.md)
- [Actions](07-actions.md)
- [Enums](08-enums.md)
