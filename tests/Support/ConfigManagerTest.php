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
