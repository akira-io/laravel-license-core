<?php

declare(strict_types=1);

use Akira\LaravelLicense\Exceptions\ActivationLimitReachedException;
use Akira\LaravelLicense\Exceptions\DomainBlockedException;
use Akira\LaravelLicense\Exceptions\DomainNotAllowedException;
use Akira\LaravelLicense\Exceptions\InsufficientCreditsException;
use Akira\LaravelLicense\Exceptions\LicenseException;
use Akira\LaravelLicense\Exceptions\LicenseExpiredException;
use Akira\LaravelLicense\Exceptions\LicenseNotFoundException;
use Akira\LaravelLicense\Exceptions\LicenseNotLoadedException;
use Akira\LaravelLicense\Exceptions\LicenseRevokedException;
use Akira\LaravelLicense\Exceptions\LicenseSuspendedException;
use Akira\LaravelLicense\Exceptions\UsageNotConfiguredException;
use Akira\LaravelLicense\Exceptions\VersionNotCoveredException;

it('all exceptions extend LicenseException', function () {
    expect(new LicenseNotFoundException('test'))->toBeInstanceOf(LicenseException::class)
        ->and(new LicenseNotLoadedException('test'))->toBeInstanceOf(LicenseException::class)
        ->and(new LicenseRevokedException('test'))->toBeInstanceOf(LicenseException::class)
        ->and(new LicenseSuspendedException('test'))->toBeInstanceOf(LicenseException::class)
        ->and(new LicenseExpiredException('test'))->toBeInstanceOf(LicenseException::class)
        ->and(new ActivationLimitReachedException('test'))->toBeInstanceOf(LicenseException::class)
        ->and(new DomainBlockedException('test'))->toBeInstanceOf(LicenseException::class)
        ->and(new DomainNotAllowedException('test'))->toBeInstanceOf(LicenseException::class)
        ->and(new InsufficientCreditsException('test'))->toBeInstanceOf(LicenseException::class)
        ->and(new UsageNotConfiguredException('test'))->toBeInstanceOf(LicenseException::class)
        ->and(new VersionNotCoveredException('test'))->toBeInstanceOf(LicenseException::class);
});

it('LicenseNotFoundException can be created', function () {
    $exception = LicenseNotFoundException::create();
    
    expect($exception)->toBeInstanceOf(LicenseNotFoundException::class)
        ->and($exception->getMessage())->toBe(__('laravel-license::license.exceptions.license_not_found'));
});

it('LicenseNotLoadedException can be created', function () {
    $exception = LicenseNotLoadedException::create();
    
    expect($exception)->toBeInstanceOf(LicenseNotLoadedException::class)
        ->and($exception->getMessage())->toBe(__('laravel-license::license.exceptions.license_not_loaded'));
});

it('LicenseRevokedException can be created', function () {
    $exception = LicenseRevokedException::create();
    
    expect($exception)->toBeInstanceOf(LicenseRevokedException::class)
        ->and($exception->getMessage())->toBe(__('laravel-license::license.exceptions.license_revoked'));
});

it('LicenseSuspendedException can be created', function () {
    $exception = LicenseSuspendedException::create();
    
    expect($exception)->toBeInstanceOf(LicenseSuspendedException::class)
        ->and($exception->getMessage())->toBe(__('laravel-license::license.exceptions.license_suspended'));
});

it('LicenseExpiredException can be created', function () {
    $exception = LicenseExpiredException::create();
    
    expect($exception)->toBeInstanceOf(LicenseExpiredException::class)
        ->and($exception->getMessage())->toBe(__('laravel-license::license.exceptions.license_expired'));
});

it('LicenseExpiredException can be created with specific date', function () {
    $date = '2024-12-31';
    $exception = LicenseExpiredException::create($date);
    
    expect($exception)->toBeInstanceOf(LicenseExpiredException::class)
        ->and($exception->getMessage())->toBe(__('laravel-license::license.exceptions.license_expired', ['date' => $date]));
});

it('ActivationLimitReachedException can be created', function () {
    $exception = ActivationLimitReachedException::create();
    
    expect($exception)->toBeInstanceOf(ActivationLimitReachedException::class)
        ->and($exception->getMessage())->toBe(__('laravel-license::license.exceptions.activation_limit_reached'));
});

it('DomainBlockedException can be created with domain', function () {
    $exception = DomainBlockedException::forDomain('blocked.com');
    
    expect($exception)->toBeInstanceOf(DomainBlockedException::class)
        ->and($exception->getMessage())->toBe(__('laravel-license::license.exceptions.domain_blocked', ['domain' => 'blocked.com']));
});

it('DomainNotAllowedException can be created with domain', function () {
    $exception = DomainNotAllowedException::forDomain('notallowed.com');
    
    expect($exception)->toBeInstanceOf(DomainNotAllowedException::class)
        ->and($exception->getMessage())->toBe(__('laravel-license::license.exceptions.domain_not_allowed', ['domain' => 'notallowed.com']));
});

it('InsufficientCreditsException can be created', function () {
    $exception = InsufficientCreditsException::create(100, 50);
    
    expect($exception)->toBeInstanceOf(InsufficientCreditsException::class)
        ->and($exception->getMessage())->toBe(__('laravel-license::license.exceptions.insufficient_credits', [
            'required' => 100,
            'available' => 50,
        ]));
});

it('UsageNotConfiguredException can be created', function () {
    $exception = UsageNotConfiguredException::create();
    
    expect($exception)->toBeInstanceOf(UsageNotConfiguredException::class)
        ->and($exception->getMessage())->toBe(__('laravel-license::license.exceptions.usage_not_configured'));
});

it('VersionNotCoveredException can be created', function () {
    $exception = VersionNotCoveredException::forVersion('2.0.0');
    
    expect($exception)->toBeInstanceOf(VersionNotCoveredException::class)
        ->and($exception->getMessage())->toBe(__('laravel-license::license.exceptions.version_not_covered', ['version' => '2.0.0']));
});

it('exceptions can be caught as LicenseException', function () {
    try {
        throw LicenseNotFoundException::create();
    } catch (LicenseException $e) {
        expect($e)->toBeInstanceOf(LicenseException::class)
            ->and($e->getMessage())->toBe(__('laravel-license::license.exceptions.license_not_found'));
    }
});
