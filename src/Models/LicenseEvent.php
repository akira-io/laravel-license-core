<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Models;

use Akira\LaravelLicense\Support\ConfigManager;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class LicenseEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'license_id',
        'type',
        'payload',
        'created_at',
    ];

    /** @var array<string, class-string|string> */
    protected $casts = [
        'payload' => AsArrayObject::class,
        'created_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return resolve(ConfigManager::class)->getEventsTable();
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }
}
