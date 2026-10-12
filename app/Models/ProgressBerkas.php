<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressBerkas extends Model
{
    protected $table = 'progress_berkas';

    protected $fillable = [
        'user_id',
        'nomor_usulan',
        'jenis_jabatan',
        'jenjang',
        'status_verifikasi',
        'status_surat_usulan',
        'status_pengelola',
        'status_ukom',
        'status_instansi_pembina',
        'status_pengumuman',
        'catatan',
        'tanggal_usulan',
    ];

    protected $casts = [
        'tanggal_usulan' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Daftar semua tahapan alur
     */
    public static function tahapan(): array
    {
        return [
            ['key' => 'status_verifikasi',       'label' => 'Verifikasi',               'icon' => '🔍', 'color' => 'blue'],
            ['key' => 'status_surat_usulan',      'label' => 'Akses Surat Usulan Ukom',  'icon' => '📄', 'color' => 'indigo'],
            ['key' => 'status_pengelola',         'label' => 'Pengelola',                'icon' => '🏢', 'color' => 'orange'],
            ['key' => 'status_ukom',              'label' => 'Uji Kompetensi',           'icon' => '🎓', 'color' => 'teal'],
            ['key' => 'status_instansi_pembina',  'label' => 'Instansi Pembina',         'icon' => '🏛️', 'color' => 'purple'],
            ['key' => 'status_pengumuman',        'label' => 'Pengumuman',               'icon' => '📢', 'color' => 'green'],
        ];
    }

    /**
     * Hitung persentase keseluruhan progress
     */
    public function progressPersen(): int
    {
        $tahapan = self::tahapan();
        $selesai = 0;
        foreach ($tahapan as $t) {
            if ($this->{$t['key']} == 2) $selesai++;
        }
        return (int) round(($selesai / count($tahapan)) * 100);
    }

    /**
     * Dapatkan label status
     */
    public static function labelStatus(int $status): string
    {
        return match($status) {
            0 => 'Belum Mulai',
            1 => 'Sedang Diproses',
            2 => 'Selesai',
            3 => 'Ditolak',
            default => '-',
        };
    }

    /**
     * Dapatkan class badge status
     */
    public static function badgeStatus(int $status): string
    {
        return match($status) {
            0 => 'badge-pending',
            1 => 'badge-process',
            2 => 'badge-done',
            3 => 'badge-rejected',
            default => 'badge-pending',
        };
    }
}
