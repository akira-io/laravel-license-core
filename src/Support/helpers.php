<?php

declare(strict_types=1);

use Akira\LaravelLicense\Facades\License;

if (! function_exists('license')) {
    function license(): License
    {
        return app(License::class);
    }
}
