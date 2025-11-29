<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Facades;

use Akira\LaravelLicense\LaravelLicense;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin LaravelLicense
 *
 * @see LaravelLicense
 */
final class License extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LaravelLicense::class;
    }
}
