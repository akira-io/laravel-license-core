<?php

declare(strict_types=1);

use Akira\LaravelLicense\Support\ConfigManager;
use Akira\LaravelLicense\Support\KeyGenerator;
use Illuminate\Support\Sleep;

test('generates uuid format key with prefix', function () {
    $manager = app()->make(ConfigManager::class);
    $generator = new KeyGenerator($manager);

    $key = $generator->generate();

    expect($key)
        ->toBeString()
        ->toStartWith('LIC-');
});

test('generates key with uuid format', function () {
    $manager = app()->make(ConfigManager::class);
    $generator = new KeyGenerator($manager);

    $key = $generator->generate();

    // UUID format: 8-4-4-4-12 hex characters
    expect($key)
        ->toMatch('/^LIC-[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i');
});

test('generates unique keys', function () {
    $manager = app()->make(ConfigManager::class);
    $generator = new KeyGenerator($manager);

    $key1 = $generator->generate();
    $key2 = $generator->generate();
    $key3 = $generator->generate();

    expect($key1)
        ->not->toBe($key2)
        ->and($key2)->not->toBe($key3)
        ->and($key1)->not->toBe($key3);
});

test('generates sequential format key', function () {
    config(['license.key_generation' => [
        'prefix' => 'LIC',
        'format' => 'sequential',
    ]]);

    $manager = app()->make(ConfigManager::class);
    $generator = new KeyGenerator($manager);

    $key = $generator->generate();

    expect($key)
        ->toBeString()
        ->toStartWith('LIC-')
        ->toMatch('/^LIC-\d{10}[A-Za-z0-9]{8}$/');
});

test('generates key without prefix when prefix is empty', function () {
    config(['license.key_generation' => [
        'prefix' => '',
        'format' => 'uuid',
    ]]);

    $manager = app()->make(ConfigManager::class);
    $generator = new KeyGenerator($manager);

    $key = $generator->generate();

    expect($key)
        ->toBeString()
        ->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i')
        ->not->toContain('-LIC-');
});

test('sequential keys have different timestamps when generated at different times', function () {
    config(['license.key_generation' => [
        'prefix' => 'LIC',
        'format' => 'sequential',
    ]]);

    $manager = app()->make(ConfigManager::class);
    $generator = new KeyGenerator($manager);

    $key1 = $generator->generate();

    Sleep::sleep(1);

    $key2 = $generator->generate();

    expect($key1)->not->toBe($key2);
});
