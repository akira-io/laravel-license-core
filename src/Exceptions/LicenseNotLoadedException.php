<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Exceptions;

final class LicenseNotLoadedException extends LicenseException
{
    public static function create(): self
    {
        return new self(__('laravel-license-core::license.exceptions.license_not_loaded'));
    }
}
