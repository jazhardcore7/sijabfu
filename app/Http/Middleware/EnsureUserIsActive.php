<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * User yang dinonaktifkan Super Admin dikeluarkan pada request berikutnya (FR-USR-1).
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // is_active null (atribut belum dimuat) dianggap aktif; hanya false yang ditolak
        if ($user && $user->is_active === false) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['nip' => 'Akun Anda dinonaktifkan. Hubungi Super Admin.']);
        }

        return $next($request);
    }
}
