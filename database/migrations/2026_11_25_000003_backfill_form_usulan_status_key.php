<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Pemetaan status lama [ASSUMPTION PRD 11.4]:
     * 1, 2 -> verifikasi; 3 -> disetujui; 4 -> ditetapkan; 5 -> selesai.
     */
    private const MAP = [
        1 => 'verifikasi',
        2 => 'verifikasi',
        3 => 'disetujui',
        4 => 'ditetapkan',
        5 => 'selesai',
    ];

    public function up(): void
    {
        foreach (self::MAP as $legacy => $key) {
            DB::table('form_usulan')
                ->whereNull('status_key')
                ->where('status', $legacy)
                ->update(['status_key' => $key]);
        }
    }

    public function down(): void
    {
        DB::table('form_usulan')->update(['status_key' => null]);
    }
};
