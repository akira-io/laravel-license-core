<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Exceptions;

final class UsageNotConfiguredException extends LicenseException
{
    public static function create(): self
    {
        return new self(__('laravel-license-core::license.exceptions.usage_not_configured'));
    }

    public static function forCreditsLicense(): self
    {
        return new self(__('laravel-license-core::license.exceptions.usage_not_configured_for_credits'));
    }
}
