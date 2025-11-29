<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\ValueObjects;

use Akira\LaravelLicense\Enums\LicenseStatus;
use Akira\LaravelLicense\Enums\LicenseType;
use Carbon\CarbonInterface;

final readonly class LicenseData
{
    /**
     * @param  array<string, mixed>|null  $scopes
     * @param  array<string, mixed>|null  $meta
     */
    public function __construct(
        public string $key,
        public LicenseType $type,
        public LicenseStatus $status,
        public int $maxActivations,
        public int $maxSeats,
        public bool $fallback,
        public ?array $scopes,
        public ?array $meta,
        public ?CarbonInterface $expiresAt,
        public ?CarbonInterface $graceEndsAt,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $key = $data['key'];
        assert(is_string($key));

        $type = $data['type'];
        if ($type instanceof LicenseType) {
            $typeEnum = $type;
        } else {
            assert(is_string($type));
            $typeEnum = LicenseType::from($type);
        }

        $status = $data['status'];
        if ($status instanceof LicenseStatus) {
            $statusEnum = $status;
        } else {
            assert(is_string($status));
            $statusEnum = LicenseStatus::from($status);
        }

        $maxActivations = $data['max_activations'];
        assert(is_int($maxActivations));

        $maxSeats = $data['max_seats'];
        assert(is_int($maxSeats));

        $fallback = $data['fallback'];
        assert(is_bool($fallback));

        $scopes = $data['scopes'] ?? null;
        if ($scopes !== null) {
            assert(is_array($scopes));
            /** @var array<string, mixed> $scopes */
        }

        $meta = $data['meta'] ?? null;
        if ($meta !== null) {
            assert(is_array($meta));
            /** @var array<string, mixed> $meta */
        }

        $expiresAt = $data['expires_at'] ?? null;
        assert($expiresAt instanceof CarbonInterface || $expiresAt === null);

        $graceEndsAt = $data['grace_ends_at'] ?? null;
        assert($graceEndsAt instanceof CarbonInterface || $graceEndsAt === null);

        return new self(
            key: $key,
            type: $typeEnum,
            status: $statusEnum,
            maxActivations: $maxActivations,
            maxSeats: $maxSeats,
            fallback: $fallback,
            scopes: $scopes,
            meta: $meta,
            expiresAt: $expiresAt,
            graceEndsAt: $graceEndsAt,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'type' => $this->type->value,
            'status' => $this->status->value,
            'max_activations' => $this->maxActivations,
            'max_seats' => $this->maxSeats,
            'fallback' => $this->fallback,
            'scopes' => $this->scopes,
            'meta' => $this->meta,
            'expires_at' => $this->expiresAt,
            'grace_ends_at' => $this->graceEndsAt,
        ];
    }
}
