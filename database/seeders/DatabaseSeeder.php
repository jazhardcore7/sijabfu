<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ProgressBerkas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Admin (Tim Kerja atau PIC)
        User::updateOrCreate(
            ['nip' => '198501012010011001'],
            [
                'name' => 'Admin Tim Kerja (PIC)',
                'email' => 'admin.jf@bps.go.id',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'admin',
                'jabatan' => 'PIC Pembinaan Jabatan Fungsional',
                'unit_kerja' => 'Biro SDM - BPS RI',
            ]
        );

        // 2. Akun Pegawai (Pengusul / Akses Portal)
        $pegawai = User::updateOrCreate(
            ['nip' => '199505152020121002'],
            [
                'name' => 'Budi Pratama, S.Tr.Stat.',
                'email' => 'pegawai@bps.go.id',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'pegawai',
                'jabatan' => 'Statistisi Ahli Pertama',
                'unit_kerja' => 'BPS RI',
            ]
        );

        // 3. Seed demo progress berkas untuk pegawai
        ProgressBerkas::updateOrCreate(
            ['nomor_usulan' => 'BPS-JF-2026-001234'],
            [
                'user_id' => $pegawai->id,
                'jenis_jabatan' => 'Statistisi',
                'jenjang' => 'Ahli Pertama',
                // Simulasi: tahap 1-3 selesai, tahap 4 sedang diproses
                'status_verifikasi'      => 2, // Selesai
                'status_surat_usulan'    => 2, // Selesai
                'status_pengelola'       => 2, // Selesai
                'status_ukom'            => 1, // Sedang Diproses
                'status_instansi_pembina'=> 0, // Belum Mulai
                'status_pengumuman'      => 0, // Belum Mulai
                'catatan' => 'Jadwal Uji Kompetensi: 20 Oktober 2026. Harap mempersiapkan dokumen pendukung.',
                'tanggal_usulan' => now()->subDays(30),
            ]
        );
    }
}
