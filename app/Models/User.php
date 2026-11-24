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
    ];

    public const ROLE_PEGAWAI = 'pegawai';

    public const ROLE_TIM_JF = 'tim_jf';

    public const ROLE_SUPER_ADMIN = 'super_admin';

    /**
     * Label tingkatan user yang ditampilkan di profil.
     */
    public const LEVEL_LABELS = [
        self::ROLE_PEGAWAI => 'Pegawai',
        self::ROLE_TIM_JF => 'Tim Pembinaan JF',
        self::ROLE_SUPER_ADMIN => 'Super Admin',
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
