<?php

declare(strict_types=1);

use Akira\LaravelLicense\Facades\License;

test('license helper returns LaravelLicense facade instance', function () {
    $result = license();

    expect($result)->toBeInstanceOf(License::class);
});

test('license helper returns instance from container', function () {
    $helper = license();

    expect($helper)->toBeInstanceOf(License::class)
        ->and(app(License::class))->toBeInstanceOf(License::class);
});

test('license helper function exists', function () {
    expect(function_exists('license'))->toBeTrue();
});
