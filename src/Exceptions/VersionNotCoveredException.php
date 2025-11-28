<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Exceptions;

final class VersionNotCoveredException extends LicenseException
{
    public static function forVersion(string $version): self
    {
        return new self(__('laravel-license-core::license.exceptions.version_not_covered', ['version' => $version]));
    }
}
