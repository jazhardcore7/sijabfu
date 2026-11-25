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
 * FR-SATKER-3: filter dan pengisian satuan kerja (tipe, kode satker, provinsi).
 */
class UserController extends Controller
{
    /** nilai filter satker untuk user yang belum punya data satker */
    public const SATKER_KOSONG = 'kosong';

    public function index(Request $request)
    {
        $role = $request->query('role');
        $keyword = trim((string) $request->query('q', ''));
        $satker = (string) $request->query('satker', '');
        $provinsi = (string) $request->query('provinsi', '');
        $provinsiList = config('sijabfu.provinsi');

        $users = User::query()
            ->when(array_key_exists((string) $role, User::LEVEL_LABELS), fn ($q) => $q->where('role', $role))
            ->when($keyword !== '', function ($q) use ($keyword) {
                $like = '%'.$keyword.'%';
                $q->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('nip', 'like', $like)->orWhere('email', 'like', $like));
            })
            ->when($satker === self::SATKER_KOSONG, fn ($q) => $q->whereNull('satker_tipe'))
            ->when(array_key_exists($satker, User::SATKER_LABELS), fn ($q) => $q->where('satker_tipe', $satker))
            ->when(array_key_exists($provinsi, $provinsiList), fn ($q) => $q->where('provinsi_kode', $provinsi))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users', [
            'users' => $users,
            'roles' => User::LEVEL_LABELS,
            'satkerLabels' => User::SATKER_LABELS,
            'provinsiList' => $provinsiList,
            'satkerMissing' => User::whereNull('satker_tipe')->count(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $provinsiList = config('sijabfu.provinsi');

        $validated = $request->validate([
            'role' => ['required', Rule::in(array_keys(User::LEVEL_LABELS))],
            'is_active' => ['required', 'boolean'],
            'satker_tipe' => ['sometimes', 'nullable', Rule::in(array_keys(User::SATKER_LABELS))],
            'satker_kode' => ['sometimes', 'nullable', 'regex:/^\d{4}$/'],
            'provinsi_kode' => ['sometimes', 'nullable', Rule::in(array_map('strval', array_keys($provinsiList)))],
        ], [
            'satker_kode.regex' => 'Kode satker harus 4 digit angka.',
            'provinsi_kode.in' => 'Provinsi tidak dikenal.',
            'satker_tipe.in' => 'Tipe satuan kerja tidak dikenal.',
        ]);

        $newRole = $validated['role'];
        $newActive = (bool) $validated['is_active'];
        $actor = Auth::user();

        // Field satker yang tidak dikirim tetap memakai nilai tersimpan
        $satker = [
            'satker_tipe' => $request->has('satker_tipe') ? ($validated['satker_tipe'] ?? null) : $user->satker_tipe,
            'satker_kode' => $request->has('satker_kode') ? ($validated['satker_kode'] ?? null) : $user->satker_kode,
            'provinsi_kode' => $request->has('provinsi_kode') ? ($validated['provinsi_kode'] ?? null) : $user->provinsi_kode,
        ];
        if ($satker['satker_tipe'] === User::SATKER_PUSAT) {
            $satker['provinsi_kode'] = null;
        }

        if ($error = $this->satkerError($newRole, $satker)) {
            return back()->withInput()->with('error', $error);
        }

        // Hanya relevan bila target saat ini Super Admin AKTIF dan perubahan ini mengurangi jumlahnya
        $demotesSuperAdmin = $user->isSuperAdmin() && $user->is_active
            && ($newRole !== User::ROLE_SUPER_ADMIN || ! $newActive);

        if ($demotesSuperAdmin && $user->is($actor)) {
            return back()->with('error', 'Anda tidak dapat menurunkan role atau menonaktifkan akun Anda sendiri.');
        }

        if ($demotesSuperAdmin && $this->activeSuperAdminCount() <= 1) {
            return back()->with('error', 'Super Admin aktif terakhir tidak dapat diturunkan atau dinonaktifkan.');
        }

        if ($user->is($actor) && ! $newActive) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->forceFill(['role' => $newRole, 'is_active' => $newActive] + $satker)->save();

        // filter daftar dari query string (bukan dari isian form, yang juga bernama "role")
        $filters = array_filter([
            'q' => $request->query('q'),
            'role' => $request->query('role'),
            'satker' => $request->query('satker'),
            'provinsi' => $request->query('provinsi'),
            'page' => $request->query('page'),
        ], fn ($v) => $v !== null && $v !== '');

        return redirect()->route('admin.users', $filters)
            ->with('success', "Data {$user->name} diperbarui: ".User::LEVEL_LABELS[$newRole].', '.($newActive ? 'aktif' : 'nonaktif')
                .', satker '.(User::SATKER_LABELS[$satker['satker_tipe']] ?? 'belum diisi').'.');
    }

    /**
     * FR-SATKER-3: aturan konsistensi role dan satuan kerja.
     */
    private function satkerError(string $role, array $satker): ?string
    {
        $tipe = $satker['satker_tipe'];
        $prov = $satker['provinsi_kode'];
        $kode = $satker['satker_kode'];

        if ($role === User::ROLE_ADMIN_SATKER && ! in_array($tipe, [User::SATKER_PROVINSI, User::SATKER_KABKOTA], true)) {
            return 'Admin Satuan Kerja Provinsi harus memiliki satuan kerja Provinsi atau Kabupaten/Kota.';
        }

        if ($role === User::ROLE_ADMIN_SATKER && $prov === null) {
            return 'Admin Satuan Kerja Provinsi wajib memiliki provinsi.';
        }

        if (in_array($tipe, [User::SATKER_PROVINSI, User::SATKER_KABKOTA], true) && $prov === null) {
            return 'Satuan kerja Provinsi atau Kabupaten/Kota wajib memiliki provinsi.';
        }

        if ($kode !== null && $prov !== null && substr($kode, 0, 2) !== $prov) {
            return 'Kode satker '.$kode.' tidak berada di provinsi yang dipilih.';
        }

        return null;
    }

    private function activeSuperAdminCount(): int
    {
        return User::where('role', User::ROLE_SUPER_ADMIN)->where('is_active', true)->count();
    }
}
