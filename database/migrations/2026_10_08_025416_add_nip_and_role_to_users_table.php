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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip', 25)->nullable()->unique()->after('id');
            $table->string('role', 20)->default('pegawai')->after('password'); // admin or pegawai
            $table->string('jabatan')->nullable()->after('role');
            $table->string('unit_kerja')->nullable()->after('jabatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nip', 'role', 'jabatan', 'unit_kerja']);
        });
    }
};
