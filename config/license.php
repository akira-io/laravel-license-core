<?php

declare(strict_types=1);

use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseActivation;
use Akira\LaravelLicense\Models\LicenseEvent;
use Akira\LaravelLicense\Models\LicenseUsage;

return [
    /*
    |--------------------------------------------------------------------------
    | License Tables
    |--------------------------------------------------------------------------
    |
    | This array defines the table names used by the license system. You can
    | customize these names if you have naming conflicts or prefer different
    | conventions for your database tables.
    |
    */
    'tables' => [
        'licenses' => 'licenses',
        'activations' => 'license_activations',
        'usages' => 'license_usages',
        'events' => 'license_events',
    ],

    /*
    |--------------------------------------------------------------------------
    | License Models
    |--------------------------------------------------------------------------
    |
    | This array specifies the Eloquent models used throughout the license
    | system. You can customize these to use your own model classes, which is
    | useful if you need to extend the base models with custom functionality.
    |
    */
    'models' => [
        'license' => License::class,
        'activation' => LicenseActivation::class,
        'usage' => LicenseUsage::class,
        'event' => LicenseEvent::class,
    ],

];
