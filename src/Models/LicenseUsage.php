<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Models;

use Akira\LaravelLicense\Database\Factories\LicenseUsageFactory;
use Akira\LaravelLicense\Support\ConfigManager;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $license_id
 * @property int $consumed_units
 * @property int $limit
 * @property CarbonInterface $created_at
 *
 * @method static LicenseUsageFactory factory()
 */
final class LicenseUsage extends Model
{
    /** @use HasFactory<LicenseUsageFactory> */
    use HasFactory;

    protected $fillable = [
        'license_id',
        'consumed_units',
        'limit',
    ];

    public function getTable(): string
    {
        return resolve(ConfigManager::class)->getUsagesTable();
    }

    /** @return  BelongsTo<License, $this> */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function remaining(): int
    {
        return (int) max(0, $this->limit - $this->consumed_units);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'consumed_units' => 'integer',
            'limit' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}
