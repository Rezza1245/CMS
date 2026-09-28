<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('dinas_id')->nullable()->after('id')->constrained('dinas')->nullOnDelete();
            $table->string('code')->nullable()->after('name');
            $table->string('role')->default('admin_dinas')->after('password');
            $table->string('status')->default('aktif')->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['dinas_id']);
            $table->dropColumn(['dinas_id', 'code', 'role', 'status']);
        });
    }
};
