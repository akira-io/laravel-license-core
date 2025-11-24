<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Support;

use Illuminate\Support\Str;

final class KeyGenerator
{
    public static function generate(): string
    {
        return 'LIC-'.Str::uuid();
    }
}
