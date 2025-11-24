<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Exceptions;

final class LicenseExpiredException extends LicenseException
{
    public static function create(?string $date = null): self
    {
        return new self(__('laravel-license::license.exceptions.license_expired', [
            'date' => $date ?? now()->toDateString(),
        ]));
    }
}
