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
        Schema::table('posts', function (Blueprint $table) {
            $table->string('direct_link')->nullable()->after('image');
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->string('direct_link')->nullable()->after('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('direct_link');
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('direct_link');
        });
    }
};
