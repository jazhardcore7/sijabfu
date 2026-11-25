<?php

namespace App\Policies;

use App\Models\ChatThread;
use App\Models\User;

/**
 * FR-RBAC-2 (pesan): Pegawai dan Admin Satuan Kerja Provinsi hanya thread miliknya (FR-SATKER-17);
 * Tim JF dan Super Admin semua thread.
 */
class ChatThreadPolicy
{
    public function view(User $user, ChatThread $thread): bool
    {
        return $user->isAdmin()
            || (($user->isPegawai() || $user->isAdminSatker()) && (int) $thread->pegawai_id === (int) $user->id);
    }

    public function reply(User $user, ChatThread $thread): bool
    {
        return $this->view($user, $thread);
    }
}
