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

    /*
    |--------------------------------------------------------------------------
    | Abuse Detection
    |--------------------------------------------------------------------------
    |
    | Configure thresholds and behavior for abuse detection based on rapid
    | activation patterns. These settings help identify suspicious activity
    | like license key sharing or automated bypass attempts.
    |
    */
    'abuse_detection' => [
        'enabled' => true,
        'window_minutes' => 10,
        'activation_threshold' => 10,
        'events_to_monitor' => ['activated'],
        'action_on_abuse' => 'log',
    ],

    /*
    |--------------------------------------------------------------------------
    | Grace Period
    |--------------------------------------------------------------------------
    |
    | Define default grace period durations (in days) for each license type.
    | Grace period allows continued usage after license expiration during
    | the specified number of days. Set to null to disable grace period
    | for a specific license type.
    |
    */
    'grace_period' => [
        'lifetime' => null,
        'annual' => null,
        'subscription' => 30,
        'trial' => 7,
        'credits' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | License Type Behavior
    |--------------------------------------------------------------------------
    |
    | Configure validation and behavior rules for each license type.
    | These settings determine how each type is validated and enforced
    | throughout the license validation pipeline.
    |
    */
    'license_types' => [
        'lifetime' => [
            'requires_activation' => false,
            'requires_update_check' => false,
            'supports_grace_period' => false,
        ],
        'annual' => [
            'requires_activation' => true,
            'requires_update_check' => true,
            'supports_grace_period' => false,
            'fallback_on_expiry' => false,
        ],
        'subscription' => [
            'requires_activation' => true,
            'requires_update_check' => true,
            'supports_grace_period' => true,
            'fallback_on_expiry' => false,
        ],
        'trial' => [
            'requires_activation' => true,
            'requires_update_check' => true,
            'supports_grace_period' => true,
            'fallback_on_expiry' => false,
        ],
        'credits' => [
            'requires_activation' => false,
            'requires_update_check' => false,
            'supports_grace_period' => false,
            'fallback_on_expiry' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Domain Validation
    |--------------------------------------------------------------------------
    |
    | Configure how domain patterns are matched against license restrictions.
    | Different pattern types offer various levels of flexibility and security.
    |
    | Supported types: 'glob', 'exact', 'regex'
    |
    */
    'domain_validation' => [
        'pattern_type' => 'glob',
        'case_sensitive' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | License Key Generation
    |--------------------------------------------------------------------------
    |
    | Configure how license keys are generated. The prefix is prepended to
    | all generated keys. Supported formats: 'uuid', 'sequential'
    |
    */
    'key_generation' => [
        'prefix' => 'LIC',
        'format' => 'uuid',
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Pipeline
    |--------------------------------------------------------------------------
    |
    | Define which validation stages are executed and in what order for each
    | pipeline. You can customize the pipeline by adding, removing, or
    | reordering stages. Custom stages must implement PipelineStage interface.
    |
    */
    'pipeline' => [
        'usage' => [
            'resolve_license',
            'status_check',
            'expiration_usage',
            'grace_period',
            'domain_check',
            'machine_check',
            'credits_usage',
            'abuse_heuristics',
        ],
        'update' => [
            'resolve_license',
            'status_check',
            'expiration_usage',
            'grace_period',
            'update_window',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Credits
    |--------------------------------------------------------------------------
    |
    | Configure credit consumption behavior for credit-based licenses.
    | These settings control how credits are validated and consumed.
    |
    */
    'credits' => [
        'allow_partial_consumption' => false,
        'allow_refund' => false,
    ],

];
