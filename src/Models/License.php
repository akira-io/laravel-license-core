<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Models;

use Akira\LaravelLicense\Database\Factories\LicenseFactory;
use Akira\LaravelLicense\Enums\LicenseStatus;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Support\ConfigManager;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $key
 * @property string $type
 * @property string $status
 * @property int $max_activations
 * @property int $max_seats
 * @property bool $fallback
 * @property array<string, mixed>|null $scopes
 * @property array<string, mixed>|null $meta
 * @property CarbonInterface|null $expires_at
 * @property CarbonInterface|null $grace_ends_at
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 *
 * @method static LicenseFactory factory()
 */
final class License extends Model
{
    /** @use HasFactory<LicenseFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'type',
        'status',
        'max_activations',
        'max_seats',
        'fallback',
        'scopes',
        'meta',
        'expires_at',
        'grace_ends_at',
    ];

    /** @return array<string, string> */
    public function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'grace_ends_at' => 'datetime',
            'fallback' => 'bool',
            'scopes' => AsArrayObject::class,
        ];
    }

    /** @return  HasMany<LicenseActivation, $this> */
    public function activations(): HasMany
    {
        return $this->hasMany(LicenseActivation::class);
    }

    /** @return  HasMany<LicenseUsage, $this> */
    public function usages(): HasMany
    {
        return $this->hasMany(LicenseUsage::class);
    }

    /** @return  HasMany<LicenseEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(LicenseEvent::class);
    }

    public function typeEnum(): LicenseType
    {
        return LicenseType::from($this->type);
    }

    public function statusEnum(): LicenseStatus
    {
        return LicenseStatus::from($this->status);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && now()->greaterThan($this->expires_at);
    }

    public function inGracePeriod(): bool
    {
        if ($this->grace_ends_at === null || $this->expires_at === null) {
            return false;
        }

        return now()->between($this->expires_at, $this->grace_ends_at);
    }

    public function getTable(): string
    {
        return resolve(ConfigManager::class)->getLicenseTable();
    }

    /** @return Attribute<array<string, mixed>|null, array<string, mixed>|null> */
    protected function meta(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value): ?array {
                if (! is_string($value) && $value !== null) {
                    return null;
                }

                if ($value === null) {
                    return null;
                }

                $decrypted = decrypt($value);
                if (! is_string($decrypted)) {
                    return null;
                }

                $decoded = json_decode($decrypted, true);

                return is_array($decoded) ? $decoded : null;
            },
            set: function (mixed $value): ?string {
                if ($value === null) {
                    return null;
                }

                if (! is_array($value)) {
                    return null;
                }

                return encrypt(json_encode($value));
            },
        );
    }
}
