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
        Schema::table('appearances', function (Blueprint $table) {
            $table->string('hero_banner')->nullable()->after('favicon');
            $table->text('hero_description')->nullable()->after('header_slogan');
        });
    }

    public function down(): void
    {
        Schema::table('appearances', function (Blueprint $table) {
            $table->dropColumn(['hero_banner', 'hero_description']);
        });
    }
};
