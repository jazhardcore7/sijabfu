<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Satker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Akun uji lokal: memberi password acak baru kepada akun demo (NIP dari direktori fiktif
 * config/sijabfu_fake.php) dan menulis daftarnya ke file Markdown di docs/ (di-.gitignore).
 * Password hanya ditulis ke file, tidak ditampilkan di terminal dan tidak disimpan di kode.
 * Ditolak di luar environment local/testing.
 */
class AkunUjiCommand extends Command
{
    protected $signature = 'sijabfu:akun-uji {--path= : File tujuan (default docs/AKUN_UJI.md)}';

    protected $description = 'Buat password acak untuk akun demo lokal dan tulis daftarnya ke docs/AKUN_UJI.md';

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('Perintah ini hanya untuk environment local/testing.');

            return self::FAILURE;
        }

        $nips = collect(config('sijabfu_fake.people', []))->pluck('nip')->filter()->values()->all();
        $users = User::whereIn('nip', $nips)->get()
            ->sortBy(fn (User $u) => [array_search($u->role, array_keys(User::LEVEL_LABELS), true), $u->satker_tipe ?? 'zz', $u->name])
            ->values();

        if ($users->isEmpty()) {
            $this->warn('Tidak ada akun demo. Jalankan seeder terlebih dahulu.');

            return self::FAILURE;
        }

        $rows = [];
        foreach ($users as $user) {
            $password = Str::password(16, symbols: false);
            $user->forceFill(['password' => Hash::make($password)])->save();
            $rows[] = [$user, $password];
        }

        $path = $this->option('path') ?: base_path('docs/AKUN_UJI.md');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $this->markdown($rows));

        $this->info(count($rows).' akun diperbarui. Daftar ditulis ke '.$path.' (password tidak ditampilkan di sini).');

        return self::SUCCESS;
    }

    /**
     * @param  array<int, array{0: User, 1: string}>  $rows
     */
    private function markdown(array $rows): string
    {
        $lines = [
            '# SIJABFU — Akun Uji Lokal',
            '',
            '*Dibuat '.now()->format('d M Y H:i').' oleh `php artisan sijabfu:akun-uji`. Hanya untuk database lokal. Data fiktif. File ini ada di `/docs`, yang di-.gitignore: jangan dibagikan atau di-commit.*',
            '',
            '## Cara masuk',
            '',
            '- **Login NIP + password:** buka `/login`, isi NIP dan password di bawah. Aktif bila `AUTH_LOCAL_ENABLED` bernilai true (default di luar production).',
            '- **LOGIN SSO (driver fake):** klik "LOGIN SSO", lalu ketik NIP di halaman simulasi. Tidak perlu password.',
            '- Password berlaku sampai perintah ini dijalankan lagi, atau database di-reset (`migrate:fresh --seed` membuat password acak baru). Jalankan ulang `php artisan sijabfu:akun-uji` untuk membuat daftar baru.',
            '',
            '## Akun',
            '',
            '| Role | Satuan kerja | Nama | NIP | Password |',
            '| --- | --- | --- | --- | --- |',
        ];

        foreach ($rows as [$user, $password]) {
            $satker = $user->satkerLabel()
                .($user->satker_kode ? ' '.$user->satker_kode : '')
                .($user->provinsi_kode ? ' (Prov. '.Satker::provinsiLabel($user->provinsi_kode).')' : '');
            $lines[] = '| '.implode(' | ', [
                $user->levelLabel().($user->is_active ? '' : ' (nonaktif)'),
                $satker,
                $user->name,
                '`'.$user->nip.'`',
                '`'.$password.'`',
            ]).' |';
        }

        $lines[] = '';
        $lines[] = '## Yang dapat diuji per role';
        $lines[] = '';
        $lines[] = '- **Pegawai (Pusat):** Pengajuan Usulan, Pelacakan Status, Regulasi, Tim Kerja, Chat Box.';
        $lines[] = '- **Pegawai (Provinsi / Kabupaten/Kota):** tidak dapat mengajukan sendiri (menu Pengajuan Usulan tidak ada, route 403). Pelacakan Status, Regulasi, Tim Kerja, Chat Box. Pelacakan read-only lengkap menyusul di S3.';
        $lines[] = '- **Admin Satuan Kerja Provinsi:** Dashboard (hanya usulan yang diajukannya), Pengajuan Usulan untuk pegawai dalam cakupan (pilih pegawai, beberapa usulan sekaligus, satu usulan aktif per pegawai, perbaikan dan kirim ulang), Regulasi, Tim Kerja. Pelacakan dan Chat Box menyusul di S3. Cakupan: pegawai Provinsi dan Kabupaten/Kota di provinsi yang sama (Contoh Pegawai Fiktif Satu dan Dua; Contoh Pegawai Provinsi Lain di luar cakupan).';
        $lines[] = '- **Tim Pembinaan JF:** Verifikasi Berkas Usulan Pegawai, Monitoring Progress UKOM, Regulasi (kelola), Chat Box (Messenger).';
        $lines[] = '- **Super Admin:** Sinkronisasi Data SSO, Monitoring Progress UKOM, Regulasi (kelola), Daftar User (role dan satuan kerja).';
        $lines[] = '';

        return implode("\n", $lines);
    }
}
