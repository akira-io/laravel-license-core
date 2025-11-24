<?php

declare(strict_types=1);

use Akira\LaravelLicense\Facades\LaravelLicense;

if (! function_exists('license')) {
    function license(): LaravelLicense
    {
        return app(LaravelLicense::class);
    }
}
