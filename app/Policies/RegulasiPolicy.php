<?php

namespace App\Policies;

use App\Models\Regulasi;
use App\Models\User;

/**
 * Regulasi: semua role melihat, preview, dan unduh; hanya Tim Pembinaan JF dan
 * Super Admin yang mengunggah, mengubah, dan menghapus (FR-REG-1, FR-REG-2).
 */
class RegulasiPolicy
{
    public function view(User $user, Regulasi $regulasi): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Regulasi $regulasi): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Regulasi $regulasi): bool
    {
        return $user->isAdmin();
    }
}
