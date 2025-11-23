<?php

declare(strict_types=1);

use Akira\LaravelLicense\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class)->in(__DIR__);

// Trait helpers for common patterns
function withDatabase()
{
    return uses(DatabaseMigrations::class);
}
