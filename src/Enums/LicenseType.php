<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Enums;

enum LicenseType: string
{
    case LIFETIME = 'lifetime';
    case ANNUAL = 'annual';
    case SUBSCRIPTION = 'subscription';
    case TRIAL = 'trial';
    case CREDITS = 'credits';
}
