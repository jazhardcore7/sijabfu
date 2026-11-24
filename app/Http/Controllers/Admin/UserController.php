<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Daftar User (FR-USR-1), khusus Super Admin: cari, filter role, ubah role, aktif/nonaktif.
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->query('role');
        $keyword = trim((string) $request->query('q', ''));

        $users = User::query()
            ->when(array_key_exists((string) $role, User::LEVEL_LABELS), fn ($q) => $q->where('role', $role))
            ->when($keyword !== '', function ($q) use ($keyword) {
                $like = '%' . $keyword . '%';
                $q->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('nip', 'like', $like)->orWhere('email', 'like', $like));
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users', [
            'users' => $users,
            'roles' => User::LEVEL_LABELS,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(array_keys(User::LEVEL_LABELS))],
            'is_active' => ['required', 'boolean'],
        ]);

        $newRole = $validated['role'];
        $newActive = (bool) $validated['is_active'];
        $actor = Auth::user();

        // Hanya relevan bila target saat ini Super Admin AKTIF dan perubahan ini mengurangi jumlahnya
        $demotesSuperAdmin = $user->isSuperAdmin() && $user->is_active
            && ($newRole !== User::ROLE_SUPER_ADMIN || !$newActive);

        if ($demotesSuperAdmin && $user->is($actor)) {
            return back()->with('error', 'Anda tidak dapat menurunkan role atau menonaktifkan akun Anda sendiri.');
        }

        if ($demotesSuperAdmin && $this->activeSuperAdminCount() <= 1) {
            return back()->with('error', 'Super Admin aktif terakhir tidak dapat diturunkan atau dinonaktifkan.');
        }

        if ($user->is($actor) && !$newActive) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->forceFill(['role' => $newRole, 'is_active' => $newActive])->save();

        // filter daftar dari query string (bukan dari isian form, yang juga bernama "role")
        $filters = array_filter([
            'q' => $request->query('q'),
            'role' => $request->query('role'),
            'page' => $request->query('page'),
        ], fn ($v) => $v !== null && $v !== '');

        return redirect()->route('admin.users', $filters)
            ->with('success', "Data {$user->name} diperbarui: " . User::LEVEL_LABELS[$newRole] . ', ' . ($newActive ? 'aktif' : 'nonaktif') . '.');
    }

    private function activeSuperAdminCount(): int
    {
        return User::where('role', User::ROLE_SUPER_ADMIN)->where('is_active', true)->count();
    }
}
