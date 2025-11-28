<?php

declare(strict_types=1);

use Akira\LaravelLicense\Facades\LaravelLicense;

test('license helper returns LaravelLicense facade instance', function () {
    $result = license();

    expect($result)->toBeInstanceOf(LaravelLicense::class);
});

test('license helper returns instance from container', function () {
    $helper = license();

    expect($helper)->toBeInstanceOf(LaravelLicense::class)
        ->and(app(LaravelLicense::class))->toBeInstanceOf(LaravelLicense::class);
});

test('license helper function exists', function () {
    expect(function_exists('license'))->toBeTrue();
});
