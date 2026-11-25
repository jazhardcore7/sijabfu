<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FR-SATKER-2: satuan kerja user (pusat, provinsi, kabkota), kode satker, dan kode provinsi.
 * Tanpa default di database: user baru tetap null sampai diisi SSO/SIMPEG atau Super Admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('satker_tipe', 10)->nullable()->index()->after('unit_kerja');
            $table->string('satker_kode', 10)->nullable()->after('satker_tipe');
            $table->string('provinsi_kode', 4)->nullable()->index()->after('satker_kode');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['satker_tipe']);
            $table->dropIndex(['provinsi_kode']);
            $table->dropColumn(['satker_tipe', 'satker_kode', 'provinsi_kode']);
        });
    }
};
