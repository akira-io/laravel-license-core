<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Models;

use Akira\LaravelLicense\Support\ConfigManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $consumed_units
 * @property int $limit
 */
final class LicenseUsage extends Model
{
    protected $fillable = [
        'license_id',
        'consumed_units',
        'limit',
    ];

    public function getTable(): string
    {
        return resolve(ConfigManager::class)->getUsagesTable();
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function remaining(): int
    {
        return (int) max(0, (int) $this->limit - (int) $this->consumed_units);
    }
}
