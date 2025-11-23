<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Models;

use Akira\LaravelLicense\Support\ConfigManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class LicenseActivation extends Model
{
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
}
