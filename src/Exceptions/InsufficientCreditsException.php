<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Exceptions;

final class InsufficientCreditsException extends LicenseException
{
    public static function create(int $required, int $available): self
    {
        return new self(__('laravel-license-core::license.exceptions.insufficient_credits', [
            'required' => $required,
            'available' => $available,
        ]));
    }
}
