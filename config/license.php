<?php

declare(strict_types=1);

use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseActivation;
use Akira\LaravelLicense\Models\LicenseEvent;
use Akira\LaravelLicense\Models\LicenseUsage;

return [
    'tables' => [
        'licenses' => 'licenses',
        'activations' => 'license_activations',
        'usages' => 'license_usages',
        'events' => 'license_events',
    ],

    'models' => [
        'license' => License::class,
        'activation' => LicenseActivation::class,
        'usage' => LicenseUsage::class,
        'event' => LicenseEvent::class,
    ],

];
