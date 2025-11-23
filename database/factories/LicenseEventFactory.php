<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Database\Factories;

use Akira\LaravelLicense\Enums\LicenseEventType;
use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LicenseEvent>
 */
final class LicenseEventFactory extends Factory
{
    protected $model = LicenseEvent::class;

    public function definition(): array
    {
        return [
            'license_id' => License::factory(),
            'type' => $this->faker->randomElement(LicenseEventType::cases())->value,
            'payload' => $this->faker->optional()->passthrough([
                'action' => $this->faker->word(),
                'timestamp' => $this->faker->unixTime(),
            ]),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function forLicense(License $license): static
    {
        return $this->state(fn (array $attributes) => [
            'license_id' => $license->id,
        ]);
    }

    public function created(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => LicenseEventType::CREATED->value,
        ]);
    }

    public function activated(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => LicenseEventType::ACTIVATED->value,
        ]);
    }

    public function deactivated(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => LicenseEventType::DEACTIVATED->value,
        ]);
    }

    public function rotated(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => LicenseEventType::ROTATED->value,
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => LicenseEventType::REVOKED->value,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => LicenseEventType::EXPIRED->value,
        ]);
    }

    public function withPayload(array $payload): static
    {
        return $this->state(fn (array $attributes) => [
            'payload' => $payload,
        ]);
    }

    public function withoutPayload(): static
    {
        return $this->state(fn (array $attributes) => [
            'payload' => null,
        ]);
    }
}
