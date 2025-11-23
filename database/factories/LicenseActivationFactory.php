<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Database\Factories;

use Akira\LaravelLicense\Models\License;
use Akira\LaravelLicense\Models\LicenseActivation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LicenseActivation>
 */
final class LicenseActivationFactory extends Factory
{
    protected $model = LicenseActivation::class;

    public function definition(): array
    {
        return [
            'license_id' => License::factory(),
            'domain' => $this->faker->optional()->domainName(),
            'machine_hash' => $this->faker->optional()->sha256(),
            'ip' => $this->faker->optional()->ipv4(),
            'user_agent' => $this->faker->optional()->userAgent(),
        ];
    }

    public function forLicense(License $license): static
    {
        return $this->state(fn (array $attributes) => [
            'license_id' => $license->id,
        ]);
    }

    public function withDomain(string $domain): static
    {
        return $this->state(fn (array $attributes) => [
            'domain' => $domain,
        ]);
    }

    public function withMachineHash(string $hash): static
    {
        return $this->state(fn (array $attributes) => [
            'machine_hash' => $hash,
        ]);
    }

    public function withIp(string $ip): static
    {
        return $this->state(fn (array $attributes) => [
            'ip' => $ip,
        ]);
    }

    public function withIpv6(): static
    {
        return $this->state(fn (array $attributes) => [
            'ip' => $this->faker->ipv6(),
        ]);
    }
}
