<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Exceptions;

final class LicenseNotFoundException extends LicenseException
{
    public static function create(): self
    {
        return new self(__('laravel-license::license.exceptions.license_not_found'));
    }
}
