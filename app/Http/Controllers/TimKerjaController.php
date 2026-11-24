<?php

namespace App\Http\Controllers;

use App\Models\User;

/**
 * Tim Kerja (FR-TK-1): daftar dan profil dari user aktif dengan role tim_jf.
 */
class TimKerjaController extends Controller
{
    public function index()
    {
        $tim = User::where('role', User::ROLE_TIM_JF)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('modules.tim-kerja', ['tim' => $tim]);
    }

    public function show(User $user)
    {
        abort_unless($user->isTimJf() && $user->is_active, 404);

        return view('modules.tim-kerja-profile', ['anggota' => $user]);
    }
}
