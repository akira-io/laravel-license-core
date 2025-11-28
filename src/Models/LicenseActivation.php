<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Models;

use Akira\LaravelLicense\Database\Factories\LicenseActivationFactory;
use Akira\LaravelLicense\Support\ConfigManager;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $license_id
 * @property string $domain
 * @property string $machine_hash
 * @property string|null $ip
 * @property string|null $user_agent
 * @property CarbonInterface|null $expires_at
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 *
 * @method static LicenseActivationFactory factory()
 */
final class LicenseActivation extends Model
{
    /** @use HasFactory<LicenseActivationFactory> */
    use HasFactory;

    protected $fillable = [
        'license_id',
        'domain',
        'machine_hash',
        'ip',
        'user_agent',
    ];

    /** @return  BelongsTo<License, $this> */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function getTable(): string
    {
        return resolve(ConfigManager::class)->getActivationsTable();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'license_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
