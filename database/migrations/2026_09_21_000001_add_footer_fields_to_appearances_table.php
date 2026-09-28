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
            $table->string('footer_title')->nullable()->after('footer_slogan');
            $table->string('footer_about_title')->nullable()->after('footer_title');
            $table->text('footer_about_text')->nullable()->after('footer_about_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appearances', function (Blueprint $table) {
            $table->dropColumn(['footer_title', 'footer_about_title', 'footer_about_text']);
        });
    }
};
