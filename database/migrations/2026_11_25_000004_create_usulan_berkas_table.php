<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usulan_berkas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_usulan_id')->constrained('form_usulan')->cascadeOnDelete();
            $table->string('jenis_persyaratan');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime', 100);
            $table->unsignedBigInteger('size');
            $table->string('pemeriksaan', 20)->nullable(); // null | sesuai | perlu_perbaikan
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usulan_berkas');
    }
};
