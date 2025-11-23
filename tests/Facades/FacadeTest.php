<?php

declare(strict_types=1);

use Akira\LaravelLicense\Facades\LaravelLicense as LaravelLicenseFacade;
use Akira\LaravelLicense\LaravelLicense;

it('resolves facade to correct class instance', function () {
    $instance = LaravelLicenseFacade::getFacadeRoot();

    expect($instance)->toBeInstanceOf(LaravelLicense::class);
});

it('facade is registered in service container', function () {
    $instance = app(LaravelLicense::class);

    expect($instance)->toBeInstanceOf(LaravelLicense::class);
});

it('has protected facade accessor method', function () {
    $reflection = new ReflectionClass(LaravelLicenseFacade::class);
    $method = $reflection->getMethod('getFacadeAccessor');

    expect($method->isProtected())->toBeTrue()
        ->and($method->isStatic())->toBeTrue();
});

it('facade accessor returns correct class name', function () {
    $reflection = new ReflectionClass(LaravelLicenseFacade::class);
    $method = $reflection->getMethod('getFacadeAccessor');
    $method->setAccessible(true);

    $accessor = $method->invoke(null);

    expect($accessor)->toBe(LaravelLicense::class);
});
