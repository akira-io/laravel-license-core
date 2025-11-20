<?php

use Akira\LaravelLicense\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

uses(TestCase::class)->in(__DIR__);

// Trait helpers for common patterns
function withDatabase()
{
    return uses(DatabaseMigrations::class);
}
