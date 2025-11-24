<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Exceptions;

final class DomainBlockedException extends LicenseException
{
    public static function forDomain(string $domain): self
    {
        return new self(__('laravel-license::license.exceptions.domain_blocked', ['domain' => $domain]));
    }
}
