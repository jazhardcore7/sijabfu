<?php

namespace App\Policies;

use App\Models\FormUsulan;
use App\Models\User;

/**
 * Skeleton policy usulan (FR-RBAC-2, FR-RBAC-3).
 * Policy berkas, pesan, dan file ditambahkan bersama modelnya pada fase berikutnya.
 */
class FormUsulanPolicy
{
    /**
     * Pemilik usulan, Tim Pembinaan JF, dan Super Admin boleh melihat.
     */
    public function view(User $user, FormUsulan $usulan): bool
    {
        return $user->isAdmin() || $usulan->user_id === $user->id;
    }

    /**
     * Hanya Pegawai yang boleh mengajukan usulan.
     */
    public function create(User $user): bool
    {
        return $user->isPegawai();
    }

    /**
     * Hanya Tim Pembinaan JF dan Super Admin yang boleh mengubah status.
     */
    public function updateStatus(User $user, FormUsulan $usulan): bool
    {
        return $user->isAdmin();
    }
}
