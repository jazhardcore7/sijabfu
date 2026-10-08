<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Jika rute membutuhkan admin tapi user bukan admin
        if ($role === 'admin' && !$user->isAdmin()) {
            return redirect()->route('home')->with('error', 'Akses ditolak: Halaman ini khusus untuk Admin Tim Kerja / PIC.');
        }

        return $next($request);
    }
}
