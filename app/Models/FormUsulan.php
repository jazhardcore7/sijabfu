<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormUsulan extends Model
{
    protected $table = 'form_usulan';

    /**
     * Status siklus hidup (PRD Revisi bagian 4), disimpan di kolom status_key.
     */
    public const STATUS_VERIFIKASI = 'verifikasi';
    public const STATUS_PERBAIKAN = 'perbaikan';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DITETAPKAN = 'ditetapkan';
    public const STATUS_SELESAI = 'selesai';

    public const STATUS_LABELS = [
        self::STATUS_VERIFIKASI => 'Verifikasi',
        self::STATUS_PERBAIKAN => 'Perbaikan',
        self::STATUS_DISETUJUI => 'Disetujui (MS)',
        self::STATUS_DITETAPKAN => 'Ditetapkan',
        self::STATUS_SELESAI => 'Selesai',
    ];

    public const HASIL_MS = 'ms';
    public const HASIL_TMS = 'tms';

    protected $fillable = [
        'user_id', 'email', 'jabatan_fungsional', 'jenis_usulan',
        'nomor_surat', 'hal_surat', 'nama_lengkap', 'nip',
        'satuan_kerja', 'link_berkas', 'link_usulan',
        'status', 'catatan',
        'status_key', 'hasil_verifikasi', 'verified_by', 'verified_at',
        'surat_usulan_path', 'sertifikat_path', 'hasil_ukom', 'submitted_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function berkas()
    {
        return $this->hasMany(UsulanBerkas::class, 'form_usulan_id');
    }

    public function statusLogs()
    {
        return $this->hasMany(UsulanStatusLog::class, 'form_usulan_id')->orderBy('id');
    }

    public function statusKeyLabel(): string
    {
        return self::STATUS_LABELS[$this->status_key] ?? '-';
    }

    /**
     * 5 tahapan alur (sesuai swimlane diagram)
     */
    public static function tahapan(): array
    {
        return [
            [
                'step'   => 1,
                'label'  => 'Mengusulkan Peserta',
                'desc'   => 'Satker/Pejabat Fungsional mengusulkan peserta beserta berkasnya (JF, jenjang, tujuan uji)',
                'aktor'  => 'Satker / Pejabat Fungsional',
                'icon'   => '📝',
                'color'  => '#E76F00',
            ],
            [
                'step'   => 2,
                'label'  => 'Verifikasi Kelayakan',
                'desc'   => 'Tim Pembinaan Jabfung melakukan verifikasi kelayakan dan kuota peserta',
                'aktor'  => 'Tim Pembinaan Jabfung',
                'icon'   => '🔍',
                'color'  => '#6895C2',
            ],
            [
                'step'   => 3,
                'label'  => 'Penetapan Jadwal',
                'desc'   => 'Unit Kompetensi menetapkan jadwal, metode, dan peserta uji kompetensi',
                'aktor'  => 'Unit Kompetensi',
                'icon'   => '📅',
                'color'  => '#5A9A5A',
            ],
            [
                'step'   => 4,
                'label'  => 'Undangan & Pemberitahuan',
                'desc'   => 'Sistem SIGMA mengirim undangan dan pemberitahuan kepada peserta',
                'aktor'  => 'Sistem / SIGMA',
                'icon'   => '📨',
                'color'  => '#4A7C99',
            ],
            [
                'step'   => 5,
                'label'  => 'Konfirmasi & Unggah Dokumen',
                'desc'   => 'Satker/Pejabat Fungsional mengkonfirmasi kehadiran dan mengunggah dokumen',
                'aktor'  => 'Satker / Pejabat Fungsional',
                'icon'   => '✅',
                'color'  => '#E76F00',
            ],
        ];
    }

    /**
     * Hitung persentase progres
     */
    public function progressPersen(): int
    {
        return intval(($this->status / 5) * 100);
    }

    /**
     * Label status teks
     */
    public static function labelStatus(int $step, int $currentStatus): string
    {
        if ($currentStatus > $step) return 'Selesai';
        if ($currentStatus === $step) return 'Sedang Diproses';
        return 'Belum Mulai';
    }

    /**
     * CSS badge class
     */
    public static function badgeStatus(int $step, int $currentStatus): string
    {
        if ($currentStatus > $step) return 'badge-done';
        if ($currentStatus === $step) return 'badge-process';
        return 'badge-pending';
    }

    /**
     * Label status keseluruhan
     */
    public function statusLabel(): string
    {
        return match($this->status) {
            1 => 'Diusulkan',
            2 => 'Sedang Diverifikasi',
            3 => 'Jadwal Ditetapkan',
            4 => 'Undangan Terkirim',
            5 => 'Selesai',
            default => 'Tidak Diketahui',
        };
    }

    public static function listJabfung(): array
    {
        return [
            'Analis Anggaran',
            'Analis Hukum',
            'Analis Kebijakan',
            'Analis Kerja Sama',
            'Analis Pengelola Keuangan APBN',
            'Analis SDM Aparatur',
            'Apoteker',
            'Arsiparis',
            'Asesor SDM Aparatur',
            'Asisten Apoteker',
            'Asisten Statistisi',
            'Auditor',
            'Bidan',
            'Dokter',
            'Dokter Gigi',
            'Dosen',
            'Penata Laksana Barang',
            'Penerjemah',
            'Pengelola Pengadaan Barang/Jasa',
            'Pengembang Teknologi Pembelajaran',
            'Penyuluh Hukum',
            'Perancang Peraturan Perundang-undangan',
            'Perawat',
            'Perekam Medis',
            'Perencana',
            'Pranata Humas',
            'Pranata Keuangan APBN',
            'Pranata Komputer',
            'Pranata SDM Aparatur',
            'Pustakawan',
            'Statistisi',
            'Terapis Gigi dan Mulut',
            'Widyaiswara',
        ];
    }

    public static function listJenisUsulan(): array
    {
        return [
            'Uji Kompetensi Kenaikan Jenjang',
            'Uji Kompetensi Perpindahan Jabatan Lain',
            'Konsultasi JF',
            'Inpassing',
        ];
    }
}

