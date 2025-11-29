# Value Objects

Immutable value objects used throughout Laravel License Core for type safety.

## Core Value Objects

### LicenseKey
Wrapper for license key string

```php
use Akira\LaravelLicense\ValueObjects\LicenseKey;

$key = LicenseKey::fromString('LIC-xxx');
echo $key->toString();
```

### DomainName
Extract and validate domain names

```php
use Akira\LaravelLicense\ValueObjects\DomainName;

$domain = DomainName::fromUrlOrHost('https://api.example.com');
// or
$domain = DomainName::fromUrlOrHost('api.example.com');
```

### MachineFingerprint
Machine identifier hash

```php
use Akira\LaravelLicense\ValueObjects\MachineFingerprint;

$fingerprint = MachineFingerprint::fromString(
    hash('sha256', gethostname())
);
```

### LicenseContext
Request context containing all validation data

```php
use Akira\LaravelLicense\ValueObjects\LicenseContext;

$context = new LicenseContext(
    key: LicenseKey::fromString('LIC-xxx'),
    domain: DomainName::fromUrlOrHost('api.example.com'),
    license: $license,  // Populated by pipeline
);
```

### LicenseScopes
Permission/feature grants

```php
use Akira\LaravelLicense\ValueObjects\LicenseScopes;

$scopes = LicenseScopes::fromArray(['read', 'write', 'api']);
```

### LicenseMeta
Encrypted metadata accessor

```php
use Akira\LaravelLicense\ValueObjects\LicenseMeta;

$meta = LicenseMeta::fromArray($encryptedData);
$customerId = $meta['customer_id'];
```

### UsageAmount
Track usage limits and consumption

```php
use Akira\LaravelLicense\ValueObjects\UsageAmount;

$amount = UsageAmount::forLicense(
    consumed: 100,
    limit: 1000
);
```

### UpdateEntitlement
Determine version availability

```php
use Akira\LaravelLicense\ValueObjects\UpdateEntitlement;

$entitlement = UpdateEntitlement::forLicense($license);
```

---

## Configuration Value Objects

Immutable configuration objects created by ConfigManager.

### AbuseDetectionConfiguration
```php
$config = app(ConfigManager::class)->getAbuseDetection();
// Properties: enabled, windowMinutes, activationThreshold, eventsToMonitor, actionOnAbuse
```

### CreditsConfiguration
```php
$config = app(ConfigManager::class)->getCredits();
// Properties: allowPartialConsumption, allowRefund
```

### DomainValidationConfiguration
```php
$config = app(ConfigManager::class)->getDomainValidation();
// Properties: patternType, caseSensitive
```

### GracePeriodConfiguration
```php
$config = app(ConfigManager::class)->getGracePeriod();
// Properties for each type: lifetime, annual, subscription, trial, credits
```

### KeyGenerationConfiguration
```php
$config = app(ConfigManager::class)->getKeyGeneration();
// Properties: prefix, format
```

### LicenseTypeConfiguration
```php
$config = app(ConfigManager::class)->getLicenseType('annual');
// Properties: requiresActivation, requiresUpdateCheck, supportsGracePeriod, fallbackOnExpiry
```

### PipelineConfiguration
```php
$config = app(ConfigManager::class)->getPipeline();
// Properties: usageStages, updateStages
```

---

**Previous**: [Usage Guide](05-usage-guide.md) | **Next**: [Actions](07-actions.md)
