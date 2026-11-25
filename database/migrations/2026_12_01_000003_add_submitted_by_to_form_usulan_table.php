<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FR-SATKER-8: pengaju usulan (Pegawai Pusat sendiri atau Admin Satuan Kerja Provinsi).
 * user_id tetap berarti pegawai yang diusulkan. Tanpa foreign key, sama seperti verified_by.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_usulan', function (Blueprint $table) {
            $table->unsignedBigInteger('submitted_by')->nullable()->index()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('form_usulan', function (Blueprint $table) {
            $table->dropIndex(['submitted_by']);
            $table->dropColumn('submitted_by');
        });
    }
};
