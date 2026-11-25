<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FR-SATKER-4: jumlah user tanpa data satker setelah sinkronisasi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sso_sync_logs', function (Blueprint $table) {
            $table->unsignedInteger('satker_missing_count')->default(0)->after('failed_count');
        });
    }

    public function down(): void
    {
        Schema::table('sso_sync_logs', function (Blueprint $table) {
            $table->dropColumn('satker_missing_count');
        });
    }
};
