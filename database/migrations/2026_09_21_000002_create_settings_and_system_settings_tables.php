<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel settings (Multi-tenant SCHEMA 4.5)
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->unique()->constrained('websites')->cascadeOnDelete();
            $table->json('general_config')->nullable();
            $table->json('media_config')->nullable();
            $table->json('privacy_config')->nullable();
            $table->json('system_config')->nullable();
            $table->json('backup_config')->nullable();
            $table->json('storage_config')->nullable();
            $table->timestamps();
        });

        // 2. Tabel system_settings (Platform-level Global Super Admin)
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('settings');
    }
};
