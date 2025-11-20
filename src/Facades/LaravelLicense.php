<?php

namespace Akira\LaravelLicense\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Akira\LaravelLicense\LaravelLicense
 */
class LaravelLicense extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Akira\LaravelLicense\LaravelLicense::class;
    }
}
