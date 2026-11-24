<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Batasi akses route ke satu atau beberapa role.
     * Contoh: role:tim_jf,super_admin
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->hasRole(...$roles)) {
            abort(403, 'Akses ditolak: halaman ini tidak tersedia untuk role Anda.');
        }

        return $next($request);
    }
}
