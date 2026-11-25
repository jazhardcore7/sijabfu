<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Akun demo satker daerah (FR-SATKER-1), data fiktif yang sama dengan config/sijabfu_fake.php.
 * Dapat dijalankan sendiri tanpa DatabaseSeeder (yang menghapus usulan akun Siti):
 *   php artisan db:seed --class=SatkerDemoSeeder
 * Nilai satker sama dengan hasil "Sinkronkan" dari direktori fiktif. Role user yang sudah ada tidak diubah.
 */
class SatkerDemoSeeder extends Seeder
{
    /** [nip, nama, email, role, jabatan, unit kerja, satker_tipe, satker_kode, provinsi_kode] */
    public const ACCOUNTS = [
        ['199001012015011001', 'Contoh Admin Satker Provinsi', 'admin.satker@example.test', User::ROLE_ADMIN_SATKER, 'Pranata SDM Aparatur Ahli Muda', 'BPS Provinsi Contoh', User::SATKER_PROVINSI, '3200', '32'],
        ['200001012024011001', 'Contoh Pegawai Fiktif Satu', 'fiktif.satu@example.test', User::ROLE_PEGAWAI, 'Pranata Komputer Ahli Pertama', 'BPS Provinsi Contoh', User::SATKER_PROVINSI, '3200', '32'],
        ['200002022024012002', 'Contoh Pegawai Fiktif Dua', 'fiktif.dua@example.test', User::ROLE_PEGAWAI, 'Statistisi Ahli Muda', 'BPS Kabupaten Contoh', User::SATKER_KABKOTA, '3273', '32'],
        ['200003032024013003', 'Contoh Pegawai Provinsi Lain', 'fiktif.tiga@example.test', User::ROLE_PEGAWAI, 'Statistisi Ahli Pertama', 'BPS Provinsi Contoh Lain', User::SATKER_PROVINSI, '3300', '33'],
    ];

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('Akun demo satker tidak dibuat di environment '.app()->environment().'.');

            return;
        }

        foreach (self::ACCOUNTS as [$nip, $name, $email, $role, $jabatan, $unit, $tipe, $kode, $prov]) {
            $user = User::firstOrNew(['nip' => $nip]);
            if (! $user->exists) {
                // Password acak yang tidak pernah ditampilkan
                $user->password = Hash::make(Str::random(40));
            }

            $user->forceFill([
                'name' => $name,
                'email' => $email,
                'role' => $user->exists ? $user->role : $role,
                'jabatan' => $jabatan,
                'unit_kerja' => $unit,
                'satker_tipe' => $tipe,
                'satker_kode' => $kode,
                'provinsi_kode' => $prov,
            ])->save();
        }
    }
}
