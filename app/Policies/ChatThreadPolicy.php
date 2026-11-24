<?php

namespace App\Policies;

use App\Models\ChatThread;
use App\Models\User;

/**
 * FR-RBAC-2 (pesan): Pegawai hanya thread miliknya; Tim JF dan Super Admin semua thread.
 */
class ChatThreadPolicy
{
    public function view(User $user, ChatThread $thread): bool
    {
        return $user->isAdmin() || ($user->isPegawai() && (int) $thread->pegawai_id === (int) $user->id);
    }

    public function reply(User $user, ChatThread $thread): bool
    {
        return $this->view($user, $thread);
    }
}
