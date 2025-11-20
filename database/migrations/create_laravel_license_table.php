<?php

declare(strict_types=1);

use Akira\LaravelLicense\Support\ConfigManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $configManager = resolve(ConfigManager::class);

        Schema::create($configManager->getLicenseTable(), function (Blueprint $table): void {
            $table->id();
            $table->uuid('key')->unique();
            $table->string('type');
            $table->string('status');
            $table->unsignedInteger('max_activations')->default(1);
            $table->unsignedInteger('max_seats')->default(1);
            $table->boolean('fallback')->default(false);
            $table->json('scopes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('grace_ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create($configManager->getActivationsTable(), function (Blueprint $table) use ($configManager): void {
            $table->id();
            $table->foreignId('license_id')->constrained($configManager->getLicenseTable())->cascadeOnDelete();
            $table->string('domain')->nullable();
            $table->string('machine_hash')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create($configManager->getUsagesTable(), function (Blueprint $table) use ($configManager): void {
            $table->id();
            $table->foreignId('license_id')->constrained($configManager->getLicenseTable())->cascadeOnDelete();
            $table->unsignedBigInteger('consumed_units')->default(0);
            $table->unsignedBigInteger('limit')->default(0);
            $table->timestamps();
        });

        Schema::create($configManager->getEventsTable(), function (Blueprint $table) use ($configManager): void {
            $table->id();
            $table->foreignId('license_id')->constrained($configManager->getLicenseTable())->cascadeOnDelete();
            $table->string('type');
            $table->json('payload')->nullable();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        $configManager = resolve(ConfigManager::class);

        Schema::dropIfExists($configManager->getEventsTable());
        Schema::dropIfExists($configManager->getUsagesTable());
        Schema::dropIfExists($configManager->getActivationsTable());
        Schema::dropIfExists($configManager->getLicenseTable());
    }
};
