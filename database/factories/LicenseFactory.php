<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Database\Factories;

use Akira\LaravelLicense\Enums\LicenseStatus;
use Akira\LaravelLicense\Enums\LicenseType;
use Akira\LaravelLicense\Models\License;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<License>
 */
final class LicenseFactory extends Factory
{
    protected $model = License::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->uuid(),
            'type' => $this->faker->randomElement(LicenseType::cases())->value,
            'status' => $this->faker->randomElement(LicenseStatus::cases())->value,
            'max_activations' => $this->faker->numberBetween(1, 10),
            'max_seats' => $this->faker->numberBetween(1, 20),
            'fallback' => $this->faker->boolean(),
            'scopes' => $this->faker->optional()->passthrough(['read', 'write', 'admin']),
            'meta' => null,
            'expires_at' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
            'grace_ends_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LicenseStatus::ACTIVE->value,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LicenseStatus::EXPIRED->value,
            'expires_at' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LicenseStatus::SUSPENDED->value,
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LicenseStatus::REVOKED->value,
        ]);
    }

    public function lifetime(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => LicenseType::LIFETIME->value,
            'expires_at' => null,
        ]);
    }

    public function annual(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => LicenseType::ANNUAL->value,
            'expires_at' => $this->faker->dateTimeBetween('+1 month', '+1 year'),
        ]);
    }

    public function trial(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => LicenseType::TRIAL->value,
            'expires_at' => $this->faker->dateTimeBetween('now', '+30 days'),
        ]);
    }

    public function withGracePeriod(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('-7 days', '-1 day'),
            'grace_ends_at' => $this->faker->dateTimeBetween('now', '+7 days'),
        ]);
    }

    public function withMeta(array $meta): static
    {
        return $this->state(fn (array $attributes) => [
            'meta' => $meta,
        ]);
    }
}
