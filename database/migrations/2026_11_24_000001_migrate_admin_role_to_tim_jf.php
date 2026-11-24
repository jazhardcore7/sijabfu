<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Data migration: legacy role 'admin' menjadi 'tim_jf' (PRD Revisi bagian 3).
     */
    public function up(): void
    {
        DB::table('users')->where('role', 'admin')->update(['role' => 'tim_jf']);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'tim_jf')->update(['role' => 'admin']);
    }
};
