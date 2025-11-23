<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Models;

use Akira\LaravelLicense\Database\Factories\LicenseEventFactory;
use Akira\LaravelLicense\Support\ConfigManager;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class LicenseEvent extends Model
{
    /** @use HasFactory<LicenseEventFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'license_id',
        'type',
        'payload',
        'created_at',
    ];
    

    public function getTable(): string
    {
        return resolve(ConfigManager::class)->getEventsTable();
    }

    /** @return  BelongsTo<License, $this> */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }
    
    
    /** @return array<string, class-string> */
    protected function casts(): array
    {
        
        return [
            'payload' => AsArrayObject::class,
            'created_at' => 'datetime',
        ];
    }
}
