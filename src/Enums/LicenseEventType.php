<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Enums;

enum LicenseEventType: string
{
    case CREATED = 'created';
    case ACTIVATED = 'activated';
    case DEACTIVATED = 'deactivated';
    case ROTATED = 'rotated';
    case REVOKED = 'revoked';
    case USAGE_CONSUMED = 'usage_consumed';
    case EXPIRED = 'expired';
    case ABUSE_DETECTED = 'abuse_detected';
}
