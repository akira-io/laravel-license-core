<?php

declare(strict_types=1);

use Akira\LaravelLicense\Support\ConfigManager;

it('gets table name from config', function () {
    config()->set('license.tables.licenses', 'custom_licenses');

    $manager = app(ConfigManager::class);

    expect($manager->getTableName('licenses'))->toBe('custom_licenses');
});

it('returns default table name when config is not set', function () {
    config()->set('license.tables', []);

    $manager = app(ConfigManager::class);

    expect($manager->getTableName('custom'))->toBe('license_customs');
});

it('gets licenses table name', function () {
    config()->set('license.tables.licenses', 'my_licenses');

    $manager = app(ConfigManager::class);

    expect($manager->getLicenseTable())->toBe('my_licenses');
});

it('gets activations table name', function () {
    config()->set('license.tables.activations', 'my_activations');

    $manager = app(ConfigManager::class);

    expect($manager->getActivationsTable())->toBe('my_activations');
});

it('gets usages table name', function () {
    config()->set('license.tables.usages', 'my_usages');

    $manager = app(ConfigManager::class);

    expect($manager->getUsagesTable())->toBe('my_usages');
});

it('gets events table name', function () {
    config()->set('license.tables.events', 'my_events');

    $manager = app(ConfigManager::class);

    expect($manager->getEventsTable())->toBe('my_events');
});

it('gets model class from config', function () {
    config()->set('license.models.license', 'App\\Models\\CustomLicense');

    $manager = app(ConfigManager::class);

    expect($manager->getModelClass('license'))->toBe('App\\Models\\CustomLicense');
});

it('returns empty string when model class is not set', function () {
    config()->set('license.models', []);

    $manager = app(ConfigManager::class);

    expect($manager->getModelClass('nonexistent'))->toBe('');
});

it('gets config value with key', function () {
    config()->set('license.custom_key', 'custom_value');

    $manager = app(ConfigManager::class);

    expect($manager->get('custom_key'))->toBe('custom_value');
});

it('gets config value with default', function () {
    $manager = app(ConfigManager::class);

    expect($manager->get('nonexistent_key', 'default_value'))->toBe('default_value');
});

it('gets config value with null default', function () {
    $manager = app(ConfigManager::class);

    expect($manager->get('nonexistent_key'))->toBeNull();
});

it('gets nested config value', function () {
    config()->set('license.nested.deep.value', 'deep_value');

    $manager = app(ConfigManager::class);

    expect($manager->get('nested.deep.value'))->toBe('deep_value');
});

it('is registered as singleton', function () {
    $manager1 = app(ConfigManager::class);
    $manager2 = app(ConfigManager::class);

    expect($manager1)->toBe($manager2);
});

it('gets abuse detection config array', function () {
    $manager = app(ConfigManager::class);

    $config = $manager->getAbuseDetectionConfig();

    expect($config)->toBeArray()
        ->and($config['enabled'])->toBeTrue()
        ->and($config['window_minutes'])->toBe(10)
        ->and($config['activation_threshold'])->toBe(10)
        ->and($config['events_to_monitor'])->toBe(['activated'])
        ->and($config['action_on_abuse'])->toBe('log');
});

it('gets grace period config array', function () {
    $manager = app(ConfigManager::class);

    $config = $manager->getGracePeriodConfig();

    expect($config)->toBeArray()
        ->and($config['subscription'])->toBe(30)
        ->and($config['trial'])->toBe(7);
});

it('gets license types config array', function () {
    $manager = app(ConfigManager::class);

    $config = $manager->getLicenseTypesConfig();

    expect($config)->toBeArray()
        ->and($config)->toHaveKey('lifetime')
        ->and($config)->toHaveKey('annual')
        ->and($config)->toHaveKey('subscription');
});

it('gets license type config by name', function () {
    $manager = app(ConfigManager::class);

    $config = $manager->getLicenseTypeConfig('subscription');

    expect($config)->toBeArray()
        ->and($config['requires_activation'])->toBeTrue()
        ->and($config['supports_grace_period'])->toBeTrue();
});

it('gets domain validation config array', function () {
    $manager = app(ConfigManager::class);

    $config = $manager->getDomainValidationConfig();

    expect($config)->toBeArray()
        ->and($config['pattern_type'])->toBe('glob')
        ->and($config['case_sensitive'])->toBeFalse();
});

it('gets key generation config array', function () {
    $manager = app(ConfigManager::class);

    $config = $manager->getKeyGenerationConfig();

    expect($config)->toBeArray()
        ->and($config['prefix'])->toBe('LIC')
        ->and($config['format'])->toBe('uuid');
});

it('gets pipeline config array', function () {
    $manager = app(ConfigManager::class);

    $config = $manager->getPipelineConfig();

    expect($config)->toBeArray()
        ->and($config)->toHaveKey('usage')
        ->and($config)->toHaveKey('update')
        ->and($config['usage'])->toBeArray()
        ->and($config['update'])->toBeArray();
});

it('gets credits config array', function () {
    $manager = app(ConfigManager::class);

    $config = $manager->getCreditsConfig();

    expect($config)->toBeArray()
        ->and($config['allow_partial_consumption'])->toBeFalse()
        ->and($config['allow_refund'])->toBeFalse();
});

it('gets abuse detection value object', function () {
    $manager = app(ConfigManager::class);

    $config = $manager->getAbuseDetection();

    expect($config->enabled)->toBeTrue()
        ->and($config->windowMinutes)->toBe(10)
        ->and($config->activationThreshold)->toBe(10);
});

it('gets grace period value object', function () {
    $manager = app(ConfigManager::class);

    $config = $manager->getGracePeriod();

    expect($config->subscription)->toBe(30)
        ->and($config->trial)->toBe(7);
});

it('gets license type value object by name', function () {
    $manager = app(ConfigManager::class);

    $config = $manager->getLicenseType('subscription');

    expect($config->requiresActivation)->toBeTrue()
        ->and($config->supportsGracePeriod)->toBeTrue();
});

it('gets domain validation value object', function () {
    $manager = app(ConfigManager::class);

    $config = $manager->getDomainValidation();

    expect($config->patternType)->toBe('glob')
        ->and($config->caseSensitive)->toBeFalse();
});

it('gets key generation value object', function () {
    $manager = app(ConfigManager::class);

    $config = $manager->getKeyGeneration();

    expect($config->prefix)->toBe('LIC')
        ->and($config->format)->toBe('uuid');
});

it('gets pipeline value object', function () {
    $manager = app(ConfigManager::class);

    $config = $manager->getPipeline();

    expect($config->usageStages)->toBeArray()
        ->and($config->updateStages)->toBeArray()
        ->and($config->usageStages)->toContain('abuse_heuristics');
});

it('gets credits value object', function () {
    $manager = app(ConfigManager::class);

    $config = $manager->getCredits();

    expect($config->allowPartialConsumption)->toBeFalse()
        ->and($config->allowRefund)->toBeFalse();
});
