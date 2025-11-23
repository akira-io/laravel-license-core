<?php

declare(strict_types=1);

use Akira\LaravelLicense\Commands\LaravelLicenseCommand;
use Illuminate\Support\Facades\Artisan;

it('has correct command signature', function () {
    $command = new LaravelLicenseCommand();

    expect($command->signature)->toBe('laravel-license');
});

it('has command description', function () {
    $command = new LaravelLicenseCommand();

    expect($command->description)->toBe('My command');
});

it('can be executed successfully', function () {
    $exitCode = Artisan::call('laravel-license');

    expect($exitCode)->toBe(0);
});

it('returns success status code', function () {
    $exitCode = Artisan::call('laravel-license');

    expect($exitCode)->toBe(LaravelLicenseCommand::SUCCESS);
});

it('outputs comment message', function () {
    Artisan::call('laravel-license');
    $output = Artisan::output();

    expect($output)->toContain('All done');
});

it('is registered in artisan', function () {
    $commands = Artisan::all();

    expect($commands)->toHaveKey('laravel-license');
});

it('command instance is correct class', function () {
    $commands = Artisan::all();
    $command = $commands['laravel-license'];

    expect($command)->toBeInstanceOf(LaravelLicenseCommand::class);
});
