<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1. Akun Admin (Tim Kerja atau PIC)
        User::updateOrCreate(
            ['nip' => '198501012010011001'],
            [
                'name'      => 'Admin Tim Kerja (PIC)',
                'email'     => 'admin.jf@bps.go.id',
                'password'  => \Illuminate\Support\Facades\Hash::make('password'),
                'role'      => User::ROLE_TIM_JF,
                'jabatan'   => 'PIC Pembinaan Jabatan Fungsional',
                'bidang'    => 'Biro SDM - Pembinaan Jabatan Fungsional',
                'kontak'    => 'jabatanfungsional@bps.go.id',
                'unit_kerja'=> 'Biro SDM - BPS RI',
            ]
        );

        // 1b. Akun Super Admin [ASSUMPTION PRD 11.5: NIP placeholder sampai NIP Super Admin asli dikonfirmasi]
        User::updateOrCreate(
            ['nip' => '198001012005011001'],
            [
                'name'      => 'Super Admin SIGMA JF',
                'email'     => 'superadmin.jf@bps.go.id',
                'password'  => \Illuminate\Support\Facades\Hash::make('password'),
                'role'      => User::ROLE_SUPER_ADMIN,
                'jabatan'   => 'Super Admin Pembinaan Jabatan Fungsional',
                'unit_kerja'=> 'Biro SDM - BPS RI',
            ]
        );

        // 2. Akun Pegawai (Sudah Mengisi Form Usulan)
        $pegawaiSudah = User::updateOrCreate(
            ['nip' => '199505152020121002'],
            [
                'name'      => 'Budi Pratama, S.Tr.Stat.',
                'email'     => 'budi.pratama@bps.go.id',
                'password'  => \Illuminate\Support\Facades\Hash::make('password'),
                'role'      => User::ROLE_PEGAWAI,
                'jabatan'   => 'Statistisi Ahli Pertama',
                'unit_kerja'=> 'BPS RI Pusat',
            ]
        );

        // Buat usulan jika belum ada
        \App\Models\FormUsulan::firstOrCreate(
            ['user_id' => $pegawaiSudah->id],
            [
                'email'              => 'budi.pratama@bps.go.id',
                'jabatan_fungsional' => 'Statistisi',
                'jenis_usulan'       => 'Uji Kompetensi Kenaikan Jenjang',
                'nomor_surat'        => '001/BPS/XI/2026',
                'hal_surat'          => 'Permohonan Uji Kompetensi Kenaikan Jenjang',
                'nama_lengkap'       => 'Budi Pratama, S.Tr.Stat.',
                'nip'                => '199505152020121002',
                'satuan_kerja'       => 'BPS RI Pusat',
                'link_berkas'        => 'https://laci.bps.go.id/s/test-berkas-jf-budi',
                'link_usulan'        => 'https://drive.google.com/test-usulan-budi',
                'status'             => 2, // Tahap 2: Verifikasi Kelayakan
                'status_key'         => \App\Models\FormUsulan::STATUS_VERIFIKASI, // pemetaan status lama 2
                'submitted_at'       => now(),
                'catatan'            => 'Berkas lengkap. Sedang menunggu verifikasi kelayakan kuota oleh Tim Pembinaan JF.',
            ]
        );

        // 3. Akun Demo Pegawai Baru (BELUM Mengisi Form Usulan)
        User::updateOrCreate(
            ['nip' => '199808182022032005'],
            [
                'name'      => 'Siti Rahmawati, S.Tr.Stat.',
                'email'     => 'siti.rahmawati@bps.go.id',
                'password'  => \Illuminate\Support\Facades\Hash::make('password'),
                'role'      => User::ROLE_PEGAWAI,
                'jabatan'   => 'Statistisi Ahli Pertama',
                'unit_kerja'=> 'BPS Provinsi DKI Jakarta',
            ]
        );
        // Pastikan akun Siti bersih dari form_usulan
        $pegawaiBelum = User::where('nip', '199808182022032005')->first();
        if ($pegawaiBelum) {
            \App\Models\FormUsulan::where('user_id', $pegawaiBelum->id)->delete();
        }
    }
}

