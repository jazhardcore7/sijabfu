<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progress_berkas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nomor_usulan')->unique(); // Nomor referensi usulan
            $table->string('jenis_jabatan')->nullable(); // Statistisi, Pranata Komputer, dll
            $table->string('jenjang')->nullable(); // Ahli Pertama, Ahli Muda, dll

            // 6 tahap: 0=belum mulai, 1=sedang, 2=selesai, 3=ditolak
            $table->tinyInteger('status_verifikasi')->default(0);       // Tahap 1: Verifikasi
            $table->tinyInteger('status_surat_usulan')->default(0);     // Tahap 2: Akses Surat Usulan Ukom
            $table->tinyInteger('status_pengelola')->default(0);        // Tahap 3: Pengelola
            $table->tinyInteger('status_ukom')->default(0);             // Tahap 4: Uji Kompetensi
            $table->tinyInteger('status_instansi_pembina')->default(0); // Tahap 5: Instansi Pembina
            $table->tinyInteger('status_pengumuman')->default(0);       // Tahap 6: Pengumuman

            $table->text('catatan')->nullable(); // Catatan dari admin
            $table->timestamp('tanggal_usulan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_berkas');
    }
};
