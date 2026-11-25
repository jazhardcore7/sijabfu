<?php

namespace App\Policies;

use App\Models\FormUsulan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Policy usulan (FR-RBAC-2, FR-RBAC-3, FR-SATKER-6 s.d. FR-SATKER-12).
 */
class FormUsulanPolicy
{
    public const PESAN_DAERAH = 'Pegawai satuan kerja Provinsi atau Kabupaten/Kota tidak dapat mengajukan usulan sendiri. Usulan diajukan oleh Admin Satuan Kerja Provinsi; pantau progresnya di menu Pelacakan Status.';

    public const PESAN_SATKER_KOSONG = 'Data satuan kerja Anda belum diisi. Hubungi Super Admin.';

    public const PESAN_LUAR_CAKUPAN = 'Pegawai ini di luar cakupan satuan kerja Anda.';

    /**
     * Staf, pegawai yang diusulkan, atau pengaju usulan (FR-SATKER-12).
     */
    public function view(User $user, FormUsulan $usulan): bool
    {
        return $user->isAdmin()
            || (int) $usulan->user_id === (int) $user->id
            || ($usulan->submitted_by !== null && (int) $usulan->submitted_by === (int) $user->id);
    }

    /**
     * Pengajuan mandiri oleh Pegawai (menu Pengajuan Usulan Pegawai).
     * - Pegawai Pusat: boleh (FR-SATKER-7).
     * - Pegawai Provinsi/Kabupaten/Kota atau tanpa data satker: ditolak (FR-SATKER-6),
     *   kecuali masih punya usulan aktif yang diajukannya sendiri (keputusan 11).
     */
    public function create(User $user): Response
    {
        if (! $user->isPegawai()) {
            return Response::deny('Akses ditolak: halaman ini tidak tersedia untuk role Anda.');
        }

        if ($user->isPegawaiPusat()) {
            return Response::allow();
        }

        $active = FormUsulan::activeFor($user);
        if ($active && (int) ($active->submitted_by ?? $active->user_id) === (int) $user->id) {
            return Response::allow();
        }

        return Response::deny($user->hasSatker() ? self::PESAN_DAERAH : self::PESAN_SATKER_KOSONG);
    }

    /**
     * Admin Satuan Kerja Provinsi mengusulkan pegawai dalam cakupannya (FR-SATKER-8, keputusan 2).
     */
    public function proposeFor(User $user, User $pegawai): Response
    {
        if (! $user->isAdminSatker()) {
            return Response::deny('Akses ditolak: halaman ini tidak tersedia untuk role Anda.');
        }

        return $pegawai->isInSatkerScopeOf($user) ? Response::allow() : Response::deny(self::PESAN_LUAR_CAKUPAN);
    }

    /**
     * Admin Satuan Kerja Provinsi mengelola usulan yang diajukannya saja (FR-SATKER-11, FR-SATKER-12, keputusan 4).
     */
    public function manageAsSatker(User $user, FormUsulan $usulan): bool
    {
        return $user->isAdminSatker() && (int) $usulan->submitted_by === (int) $user->id;
    }

    /**
     * Hanya Tim Pembinaan JF dan Super Admin yang boleh mengubah status.
     */
    public function updateStatus(User $user, FormUsulan $usulan): bool
    {
        return $user->isAdmin();
    }
}
