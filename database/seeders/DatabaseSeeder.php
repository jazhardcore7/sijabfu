<?php

namespace Database\Seeders;

use App\Models\FormUsulan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Akun demo hanya untuk lokal/pengujian. Password acak dan tidak ditampilkan;
        // masuk lewat halaman simulasi SSO (SSO_DRIVER=fake) dengan NIP.
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('Akun demo tidak dibuat di environment '.app()->environment().'.');

            return;
        }

        // 1. Akun Tim Pembinaan JF (PIC)
        User::updateOrCreate(
            ['nip' => '198501012010011001'],
            [
                'name' => 'Admin Tim Kerja (PIC)',
                'email' => 'admin.jf@bps.go.id',
                'password' => $this->randomPassword(),
                'role' => User::ROLE_TIM_JF,
                'jabatan' => 'PIC Pembinaan Jabatan Fungsional',
                'bidang' => 'Biro SDM - Pembinaan Jabatan Fungsional',
                'kontak' => 'jabatanfungsional@bps.go.id',
                'unit_kerja' => 'Biro SDM - BPS RI',
            ] + self::PUSAT
        );

        // 1b. Akun Super Admin [ASSUMPTION PRD 11.5: NIP placeholder sampai NIP Super Admin asli dikonfirmasi]
        User::updateOrCreate(
            ['nip' => '198001012005011001'],
            [
                'name' => 'Super Admin SIGMA JF',
                'email' => 'superadmin.jf@bps.go.id',
                'password' => $this->randomPassword(),
                'role' => User::ROLE_SUPER_ADMIN,
                'jabatan' => 'Super Admin Pembinaan Jabatan Fungsional',
                'unit_kerja' => 'Biro SDM - BPS RI',
            ] + self::PUSAT
        );

        // 2. Akun Pegawai (Sudah Mengisi Form Usulan)
        $pegawaiSudah = User::updateOrCreate(
            ['nip' => '199505152020121002'],
            [
                'name' => 'Budi Pratama, S.Tr.Stat.',
                'email' => 'budi.pratama@bps.go.id',
                'password' => $this->randomPassword(),
                'role' => User::ROLE_PEGAWAI,
                'jabatan' => 'Statistisi Ahli Pertama',
                'unit_kerja' => 'BPS RI Pusat',
            ] + self::PUSAT
        );

        // Buat usulan jika belum ada
        FormUsulan::firstOrCreate(
            ['user_id' => $pegawaiSudah->id],
            [
                'submitted_by' => $pegawaiSudah->id,
                'email' => 'budi.pratama@bps.go.id',
                'jabatan_fungsional' => 'Statistisi',
                'jenis_usulan' => 'Uji Kompetensi Kenaikan Jenjang',
                'nomor_surat' => '001/BPS/XI/2026',
                'hal_surat' => 'Permohonan Uji Kompetensi Kenaikan Jenjang',
                'nama_lengkap' => 'Budi Pratama, S.Tr.Stat.',
                'nip' => '199505152020121002',
                'satuan_kerja' => 'BPS RI Pusat',
                'link_berkas' => 'https://laci.bps.go.id/s/test-berkas-jf-budi',
                'link_usulan' => 'https://drive.google.com/test-usulan-budi',
                'status' => 2, // kolom lama; pemetaan ke status_key verifikasi
                'status_key' => FormUsulan::STATUS_VERIFIKASI,
                'submitted_at' => now(),
                'catatan' => 'Berkas lengkap. Sedang menunggu verifikasi kelayakan kuota oleh Tim Pembinaan JF.',
            ]
        );

        // 3. Akun Demo Pegawai Baru (BELUM Mengisi Form Usulan)
        User::updateOrCreate(
            ['nip' => '199808182022032005'],
            [
                'name' => 'Siti Rahmawati, S.Tr.Stat.',
                'email' => 'siti.rahmawati@bps.go.id',
                'password' => $this->randomPassword(),
                'role' => User::ROLE_PEGAWAI,
                'jabatan' => 'Statistisi Ahli Pertama',
                'unit_kerja' => 'BPS Provinsi DKI Jakarta',
                // keputusan 13: user lama tetap Pusat sampai data satker diisi
                'satker_tipe' => User::SATKER_PUSAT,
            ]
        );
        // Pastikan akun Siti bersih dari form_usulan
        $pegawaiBelum = User::where('nip', '199808182022032005')->first();
        if ($pegawaiBelum) {
            FormUsulan::where('user_id', $pegawaiBelum->id)->delete();
        }

        // 4. Admin Satuan Kerja Provinsi dan pegawai daerah (data fiktif, FR-SATKER-1)
        $this->call(SatkerDemoSeeder::class);
    }

    private const PUSAT = [
        'satker_tipe' => User::SATKER_PUSAT,
        'satker_kode' => '0000',
        'provinsi_kode' => null,
    ];

    /**
     * Password acak yang tidak pernah ditampilkan (bukan kredensial demo yang bisa ditebak).
     */
    private function randomPassword(): string
    {
        return Hash::make(Str::random(40));
    }
}
