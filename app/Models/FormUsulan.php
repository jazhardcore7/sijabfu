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
        'user_id', 'submitted_by', 'email', 'jabatan_fungsional', 'jenis_usulan',
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

    /**
     * Pengaju usulan: Pegawai Pusat sendiri atau Admin Satuan Kerja Provinsi (FR-SATKER-8).
     */
    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function berkas()
    {
        return $this->hasMany(UsulanBerkas::class, 'form_usulan_id');
    }

    public function statusLogs()
    {
        return $this->hasMany(UsulanStatusLog::class, 'form_usulan_id')->orderBy('id');
    }

    /**
     * Kelas badge warna per status (memakai kelas badge-step-N yang sudah ada).
     */
    public const STATUS_BADGES = [
        self::STATUS_PERBAIKAN => 'badge-step-1',
        self::STATUS_VERIFIKASI => 'badge-step-2',
        self::STATUS_DISETUJUI => 'badge-step-3',
        self::STATUS_DITETAPKAN => 'badge-step-4',
        self::STATUS_SELESAI => 'badge-step-5',
    ];

    public function statusBadgeClass(): string
    {
        return self::STATUS_BADGES[$this->status_key] ?? 'badge-step-1';
    }

    /**
     * Filter daftar staf: status_key dan pencarian NIP/nama (FR-VB-1, FR-MON-1).
     */
    public function scopeFilterStaf($query, ?string $status, ?string $keyword)
    {
        if ($status !== null && $status !== '' && array_key_exists($status, self::STATUS_LABELS)) {
            $query->where('status_key', $status);
        }

        if ($keyword !== null && trim($keyword) !== '') {
            $like = '%'.trim($keyword).'%';
            $query->where(fn ($q) => $q->where('nip', 'like', $like)->orWhere('nama_lengkap', 'like', $like));
        }

        return $query;
    }

    /**
     * FR-PU-6 / FR-SATKER-9: usulan aktif seorang pegawai = usulan terakhir miliknya
     * (user_id) yang belum selesai, termasuk draf. Siapa pun pengajunya.
     */
    public static function activeFor(User $pegawai): ?self
    {
        $latest = self::where('user_id', $pegawai->id)->orderByDesc('id')->first();

        return $latest && $latest->status_key !== self::STATUS_SELESAI ? $latest : null;
    }

    /**
     * Diajukan sendiri oleh pegawai yang diusulkan (atau data lama tanpa submitted_by).
     */
    public function isSelfSubmitted(): bool
    {
        return $this->submitted_by === null || (int) $this->submitted_by === (int) $this->user_id;
    }

    public function statusKeyLabel(): string
    {
        return self::STATUS_LABELS[$this->status_key] ?? 'Draf';
    }

    /**
     * Draf: data/berkas sudah disimpan tetapi belum dikirim (status_key null).
     */
    public function isDraft(): bool
    {
        return $this->status_key === null;
    }

    /**
     * Pegawai hanya boleh mengubah data dan berkas saat draf atau perbaikan (FR-PU-5).
     */
    public function isEditableByPegawai(): bool
    {
        return $this->isDraft() || $this->status_key === self::STATUS_PERBAIKAN;
    }

    /**
     * Usulan yang sudah dikirim (bukan draf); dipakai pada daftar staf.
     */
    public function scopeSubmitted($query)
    {
        return $query->whereNotNull('status_key');
    }

    /**
     * Tautan lama hanya ditampilkan bila skemanya http atau https (FR-PU-7).
     */
    public static function safeUrl(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true) ? $url : null;
    }

    /**
     * Jenis persyaratan yang belum punya file.
     */
    public function missingBerkas(): array
    {
        $required = array_keys(config('sijabfu.berkas_persyaratan'));
        $uploaded = $this->berkas()->pluck('jenis_persyaratan')->all();

        return array_values(array_diff($required, $uploaded));
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
