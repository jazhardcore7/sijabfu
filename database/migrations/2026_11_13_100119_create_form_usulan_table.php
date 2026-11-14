<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_usulan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('email');
            $table->string('jabatan_fungsional');
            $table->string('jenis_usulan');
            $table->string('nomor_surat');
            $table->string('hal_surat');
            $table->string('nama_lengkap');
            $table->string('nip', 30);
            $table->string('satuan_kerja');
            $table->text('link_berkas');
            $table->text('link_usulan')->nullable();
            // Status: 1=Diusulkan, 2=Verifikasi, 3=Jadwal, 4=Undangan, 5=Konfirmasi/Selesai
            $table->tinyInteger('status')->default(1);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_usulan');
    }
};