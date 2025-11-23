<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Database\Factories;

use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseUsage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LicenseUsage>
 */
final class LicenseUsageFactory extends Factory
{
    protected $model = LicenseUsage::class;

    public function definition(): array
    {
        $limit = $this->faker->numberBetween(100, 10000);
        $consumed = $this->faker->numberBetween(0, $limit);

        return [
            'license_id' => License::factory(),
            'consumed_units' => $consumed,
            'limit' => $limit,
        ];
    }

    public function forLicense(License $license): self
    {
        return $this->state(fn (array $attributes) => [
            'license_id' => $license->id,
        ]);
    }

    public function withLimit(int $limit): self
    {
        return $this->state(fn (array $attributes) => [
            'limit' => $limit,
            'consumed_units' => min($attributes['consumed_units'] ?? 0, $limit),
        ]);
    }

    public function withConsumed(int $consumed): self
    {
        return $this->state(fn (array $attributes) => [
            'consumed_units' => $consumed,
        ]);
    }

    public function depleted(): self
    {
        return $this->state(fn (array $attributes) => [
            'consumed_units' => $attributes['limit'],
        ]);
    }

    public function overLimit(): self
    {
        return $this->state(fn (array $attributes) => [
            'consumed_units' => $attributes['limit'] + $this->faker->numberBetween(1, 100),
        ]);
    }

    public function fresh(): self
    {
        return $this->state(fn (array $attributes) => [
            'consumed_units' => 0,
        ]);
    }
}
