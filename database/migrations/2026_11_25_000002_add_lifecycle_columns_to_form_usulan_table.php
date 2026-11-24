<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PRD Revisi bagian 4 dan 6: kolom siklus hidup usulan.
     * Kolom integer `status` lama tidak diubah.
     * verified_by tanpa constraint FK (SQLite tidak mendukung ALTER ADD FOREIGN KEY).
     */
    public function up(): void
    {
        Schema::table('form_usulan', function (Blueprint $table) {
            $table->string('status_key', 20)->nullable()->index()->after('status');
            $table->string('hasil_verifikasi', 3)->nullable()->after('status_key'); // ms | tms
            $table->unsignedBigInteger('verified_by')->nullable()->index()->after('hasil_verifikasi');
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->string('surat_usulan_path')->nullable()->after('verified_at');
            $table->string('sertifikat_path')->nullable()->after('surat_usulan_path');
            $table->text('hasil_ukom')->nullable()->after('sertifikat_path');
            $table->timestamp('submitted_at')->nullable()->after('hasil_ukom');
        });
    }

    public function down(): void
    {
        Schema::table('form_usulan', function (Blueprint $table) {
            $table->dropIndex(['status_key']);
            $table->dropIndex(['verified_by']);
        });

        Schema::table('form_usulan', function (Blueprint $table) {
            $table->dropColumn([
                'status_key', 'hasil_verifikasi', 'verified_by', 'verified_at',
                'surat_usulan_path', 'sertifikat_path', 'hasil_ukom', 'submitted_at',
            ]);
        });
    }
};
