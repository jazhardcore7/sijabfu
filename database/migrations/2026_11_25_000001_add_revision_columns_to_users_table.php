<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PRD Revisi bagian 6: kolom tambahan users.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('role');
            $table->string('sso_id')->nullable()->unique()->after('nip');
            $table->string('bidang')->nullable()->after('unit_kerja');
            $table->string('foto_path')->nullable()->after('bidang');
            $table->string('kontak')->nullable()->after('foto_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['sso_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'sso_id', 'bidang', 'foto_path', 'kontak']);
        });
    }
};
