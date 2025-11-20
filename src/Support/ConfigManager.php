<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Support;

use Illuminate\Container\Attributes\Singleton;

#[Singleton]
final class ConfigManager
{
    public function getTableName(string $key): string
    {
        return config()->string("license.tables.{$key}", "license_{$key}s");
    }

    public function getLicenseTable(): string
    {
        return $this->getTableName('licenses');
    }

    public function getActivationsTable(): string
    {
        return $this->getTableName('activations');
    }

    public function getUsagesTable(): string
    {
        return $this->getTableName('usages');
    }

    public function getEventsTable(): string
    {
        return $this->getTableName('events');
    }

    public function getModelClass(string $key): string
    {
        return config()->string("license.models.{$key}", '');
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return config("license.{$key}", $default);
    }
}
