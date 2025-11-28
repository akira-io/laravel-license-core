<?php

declare(strict_types=1);

return [
    'exceptions' => [
        'activation_limit_reached' => 'Activation limit reached for this license.',
        'domain_not_allowed' => 'Domain :domain is not allowed for this license.',
        'domain_blocked' => 'Domain :domain is blocked for this license.',
        'version_not_covered' => 'Version :version is not covered by this license.',
        'insufficient_credits' => 'Insufficient credits. Required: :required, Available: :available',
        'license_not_found' => 'License not found.',
        'license_not_loaded' => 'License must be loaded before performing this operation.',
        'license_expired' => 'License has expired on :date.',
        'annual_license_expired' => 'Annual license has expired.',
        'license_revoked' => 'License has been revoked.',
        'license_suspended' => 'License is currently suspended.',
        'usage_not_configured' => 'Usage tracking is not configured for this license.',
        'usage_not_configured_for_credits' => 'Usage not configured for credits license.',
    ],

    'status' => [
        'active' => 'Active',
        'expired' => 'Expired',
        'suspended' => 'Suspended',
        'revoked' => 'Revoked',
    ],

    'type' => [
        'perpetual' => 'Perpetual',
        'subscription' => 'Subscription',
        'trial' => 'Trial',
    ],

    'events' => [
        'activated' => 'License activated',
        'deactivated' => 'License deactivated',
        'suspended' => 'License suspended',
        'resumed' => 'License resumed',
        'revoked' => 'License revoked',
        'renewed' => 'License renewed',
        'upgraded' => 'License upgraded',
        'downgraded' => 'License downgraded',
    ],
];
