<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UsulanBerkas;

/**
 * Policy berkas usulan (FR-RBAC-2).
 */
class UsulanBerkasPolicy
{
    /**
     * Pemilik usulan, pengaju usulan, Tim Pembinaan JF, dan Super Admin boleh melihat berkas.
     */
    public function view(User $user, UsulanBerkas $berkas): bool
    {
        // Pemilik, pengaju (FR-SATKER-12), atau staf: sama dengan FormUsulanPolicy::view
        return $berkas->usulan !== null && (new FormUsulanPolicy)->view($user, $berkas->usulan);
    }

    /**
     * Pemeriksaan per berkas hanya oleh Tim Pembinaan JF dan Super Admin (FR-VB-3).
     */
    public function periksa(User $user, UsulanBerkas $berkas): bool
    {
        return $user->isAdmin();
    }
}
