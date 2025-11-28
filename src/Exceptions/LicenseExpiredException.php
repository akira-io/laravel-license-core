<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Exceptions;

final class LicenseExpiredException extends LicenseException
{
    public static function create(?string $date = null): self
    {
        return new self(__('laravel-license-core::license.exceptions.license_expired', [
            'date' => $date ?? now()->toDateString(),
        ]));
    }

    public static function withGracePeriod(): self
    {
        return new self(__('laravel-license-core::license.exceptions.license_expired_grace_period'));
    }

    public static function annual(): self
    {
        return new self(__('laravel-license-core::license.exceptions.annual_license_expired'));
    }
}
