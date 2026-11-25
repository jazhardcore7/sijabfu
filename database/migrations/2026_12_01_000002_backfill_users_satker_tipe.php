<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * FR-SATKER-2, keputusan 1: user yang sudah ada saat migrasi menjadi "pusat",
 * agar tidak ada yang kehilangan hak mengajukan usulan.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->whereNull('satker_tipe')->update(['satker_tipe' => 'pusat']);
    }

    public function down(): void
    {
        // Kolom dihapus oleh down() migrasi 2026_12_01_000001.
    }
};
