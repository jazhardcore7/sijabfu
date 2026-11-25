<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * FR-SATKER-20: usulan lama diajukan sendiri oleh pemiliknya.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('form_usulan')->whereNull('submitted_by')->update(['submitted_by' => DB::raw('user_id')]);
    }

    public function down(): void
    {
        // Kolom dihapus oleh down() migrasi 2026_12_01_000003.
    }
};
