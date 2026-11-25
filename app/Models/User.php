<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'nip',
        'email',
        'password',
        'role',
        'jabatan',
        'unit_kerja',
        'is_active',
        'sso_id',
        'bidang',
        'foto_path',
        'kontak',
        'satker_tipe',
        'satker_kode',
        'provinsi_kode',
    ];

    public const ROLE_PEGAWAI = 'pegawai';

    public const ROLE_TIM_JF = 'tim_jf';

    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLE_ADMIN_SATKER = 'admin_satker';

    /**
     * Tipe satuan kerja (FR-SATKER-2).
     */
    public const SATKER_PUSAT = 'pusat';

    public const SATKER_PROVINSI = 'provinsi';

    public const SATKER_KABKOTA = 'kabkota';

    public const SATKER_LABELS = [
        self::SATKER_PUSAT => 'Pusat',
        self::SATKER_PROVINSI => 'Provinsi',
        self::SATKER_KABKOTA => 'Kabupaten/Kota',
    ];

    /**
     * Label tingkatan user yang ditampilkan di profil.
     */
    public const LEVEL_LABELS = [
        self::ROLE_PEGAWAI => 'Pegawai',
        self::ROLE_TIM_JF => 'Tim Pembinaan JF',
        self::ROLE_SUPER_ADMIN => 'Super Admin',
        self::ROLE_ADMIN_SATKER => 'Admin Satuan Kerja Provinsi',
    ];

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Staf pengelola (Tim Pembinaan JF atau Super Admin).
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_TIM_JF, self::ROLE_SUPER_ADMIN);
    }

    public function isTimJf(): bool
    {
        return $this->role === self::ROLE_TIM_JF;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isPegawai(): bool
    {
        return $this->role === self::ROLE_PEGAWAI;
    }

    /**
     * Admin Satuan Kerja Provinsi (FR-SATKER-1). Bukan staf: isAdmin() tetap false.
     */
    public function isAdminSatker(): bool
    {
        return $this->role === self::ROLE_ADMIN_SATKER;
    }

    public function isPegawaiPusat(): bool
    {
        return $this->isPegawai() && $this->satker_tipe === self::SATKER_PUSAT;
    }

    /**
     * Pegawai satuan kerja Provinsi atau Kabupaten/Kota.
     */
    public function isPegawaiDaerah(): bool
    {
        return $this->isPegawai() && in_array($this->satker_tipe, [self::SATKER_PROVINSI, self::SATKER_KABKOTA], true);
    }

    public function hasSatker(): bool
    {
        return array_key_exists((string) $this->satker_tipe, self::SATKER_LABELS);
    }

    public function satkerLabel(): string
    {
        return self::SATKER_LABELS[$this->satker_tipe] ?? 'Belum diisi';
    }

    /**
     * Pegawai yang menjadi cakupan seorang Admin Satuan Kerja Provinsi (keputusan 2):
     * Pegawai aktif dengan satker Provinsi atau Kabupaten/Kota di provinsi yang sama.
     */
    public function scopeInSatkerScopeOf($query, User $admin)
    {
        if (! $admin->isAdminSatker() || ! $admin->provinsi_kode) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('role', self::ROLE_PEGAWAI)
            ->where('is_active', true)
            ->whereIn('satker_tipe', [self::SATKER_PROVINSI, self::SATKER_KABKOTA])
            ->where('provinsi_kode', $admin->provinsi_kode);
    }

    public function isInSatkerScopeOf(User $admin): bool
    {
        return $admin->isAdminSatker()
            && $admin->provinsi_kode !== null
            && $this->isPegawaiDaerah()
            && (bool) $this->is_active
            && $this->provinsi_kode === $admin->provinsi_kode;
    }

    public function levelLabel(): string
    {
        return self::LEVEL_LABELS[$this->role] ?? 'Pegawai';
    }

    /**
     * Route dashboard sesuai role.
     */
    public function dashboardRoute(): string
    {
        return 'dashboard';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
