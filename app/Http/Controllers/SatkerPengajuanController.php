<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidStatusTransition;
use App\Models\FormUsulan;
use App\Models\User;
use App\Services\SimpegSyncService;
use App\Services\UsulanFormService;
use App\Services\UsulanStateMachine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use RuntimeException;

/**
 * Pengajuan Usulan oleh Admin Satuan Kerja Provinsi (FR-SATKER-8 s.d. FR-SATKER-12).
 * - Pegawai dalam cakupan provinsi Admin (keputusan 2).
 * - Banyak usulan berjalan bersamaan, satu usulan aktif per pegawai (keputusan 3).
 * - Admin hanya melihat dan mengubah usulan yang diajukannya (keputusan 4).
 */
class SatkerPengajuanController extends Controller
{
    public const PESAN_AKTIF = 'Pegawai ini sudah memiliki usulan aktif.';

    public function __construct(
        private UsulanStateMachine $stateMachine,
        private UsulanFormService $form,
    ) {}

    /**
     * Daftar usulan yang diajukan Admin ini, filter status (termasuk draf), cari NIP/nama, 15 per halaman.
     */
    public function index(Request $request)
    {
        $status = (string) $request->query('status', '');
        $keyword = trim((string) $request->query('q', ''));

        $daftar = FormUsulan::where('submitted_by', Auth::id())
            ->when($status === 'draf', fn ($q) => $q->whereNull('status_key'))
            ->when($status !== 'draf', fn ($q) => $q->filterStaf($status, null))
            ->when($keyword !== '', function ($q) use ($keyword) {
                $like = '%'.$keyword.'%';
                $q->where(fn ($w) => $w->where('nip', 'like', $like)->orWhere('nama_lengkap', 'like', $like));
            })
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('satker.pengajuan.index', [
            'daftarUsulan' => $daftar,
            'statusLabels' => ['draf' => 'Draf'] + FormUsulan::STATUS_LABELS,
        ]);
    }

    /**
     * Pilih pegawai dalam cakupan untuk usulan baru.
     */
    public function pilihPegawai(Request $request)
    {
        $admin = Auth::user();
        $keyword = trim((string) $request->query('q', ''));

        $pegawai = User::inSatkerScopeOf($admin)
            ->when($keyword !== '', function ($q) use ($keyword) {
                $like = '%'.$keyword.'%';
                $q->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('nip', 'like', $like));
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $pegawai->getCollection()->each(fn (User $p) => $p->setAttribute('usulan_aktif', FormUsulan::activeFor($p)));

        return view('satker.pengajuan.pilih-pegawai', [
            'pegawaiList' => $pegawai,
            'admin' => $admin,
        ]);
    }

    public function create(User $pegawai)
    {
        Gate::authorize('proposeFor', [FormUsulan::class, $pegawai]);

        if (FormUsulan::activeFor($pegawai)) {
            return redirect()->route('satker.pengajuan.pilih-pegawai')->with('error', self::PESAN_AKTIF);
        }

        return $this->renderForm(null, $pegawai);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['pegawai_id' => ['required', 'integer']]);
        $pegawai = User::findOrFail((int) $request->input('pegawai_id'));
        Gate::authorize('proposeFor', [FormUsulan::class, $pegawai]);

        [$validated, $uploads] = $this->form->validate($request);
        $admin = Auth::user();

        try {
            // Satu usulan aktif per pegawai (FR-SATKER-9): cek dan buat dalam satu transaksi
            $usulan = DB::transaction(function () use ($pegawai, $admin, $validated, $uploads) {
                if (FormUsulan::activeFor($pegawai)) {
                    throw new RuntimeException(self::PESAN_AKTIF);
                }

                return $this->form->save(null, $pegawai, $admin, $validated, $uploads);
            });
        } catch (RuntimeException $e) {
            return redirect()->route('satker.pengajuan.pilih-pegawai')->with('error', $e->getMessage());
        }

        return ($validated['aksi'] ?? 'simpan') === 'kirim'
            ? $this->kirim($usulan, $admin)
            : redirect()->route('satker.pengajuan.edit', $usulan)->with('success', 'Data dan berkas usulan berhasil disimpan.');
    }

    public function edit(FormUsulan $usulan)
    {
        Gate::authorize('manageAsSatker', $usulan);

        return $this->renderForm($usulan, $usulan->user);
    }

    public function update(Request $request, FormUsulan $usulan): RedirectResponse
    {
        Gate::authorize('manageAsSatker', $usulan);

        if (! $usulan->isEditableByPegawai()) {
            return redirect()->route('satker.pengajuan.edit', $usulan)->with('error', 'Usulan ini sedang diproses dan tidak dapat diubah.');
        }

        [$validated, $uploads] = $this->form->validate($request);
        $admin = Auth::user();
        $usulan = $this->form->save($usulan, $usulan->user, $admin, $validated, $uploads);

        return ($validated['aksi'] ?? 'simpan') === 'kirim'
            ? $this->kirim($usulan, $admin)
            : redirect()->route('satker.pengajuan.edit', $usulan)->with('success', 'Data dan berkas usulan berhasil disimpan.');
    }

    /**
     * Kirim ulang dari perbaikan, hanya oleh Admin pengaju (FR-SATKER-11).
     */
    public function kirimUlang(FormUsulan $usulan): RedirectResponse
    {
        Gate::authorize('manageAsSatker', $usulan);

        if ($usulan->status_key !== FormUsulan::STATUS_PERBAIKAN) {
            return redirect()->route('satker.pengajuan.edit', $usulan)->with('error', 'Kirim ulang hanya dapat dilakukan saat status Perbaikan.');
        }

        if ($message = $this->form->missingMessage($usulan)) {
            return redirect()->route('satker.pengajuan.edit', $usulan)->with('error', $message);
        }

        try {
            $this->stateMachine->resubmit($usulan, Auth::user());
        } catch (InvalidStatusTransition $e) {
            return redirect()->route('satker.pengajuan.edit', $usulan)->with('error', $e->getMessage());
        }

        return redirect()->route('satker.pengajuan.index')->with('success', "Usulan {$usulan->nama_lengkap} berhasil dikirim ulang dan menunggu verifikasi.");
    }

    /**
     * Sinkron SIMPEG/SIASN untuk pegawai yang dipilih (FR-SATKER-10).
     */
    public function sinkron(Request $request, User $pegawai, SimpegSyncService $simpeg): RedirectResponse
    {
        Gate::authorize('proposeFor', [FormUsulan::class, $pegawai]);

        $usulan = $request->filled('usulan') ? FormUsulan::find((int) $request->input('usulan')) : null;
        $back = $usulan && Gate::allows('manageAsSatker', $usulan) && (int) $usulan->user_id === (int) $pegawai->id
            ? route('satker.pengajuan.edit', $usulan)
            : route('satker.pengajuan.create', $pegawai);

        $result = $simpeg->syncUser($pegawai);

        if (! $result['ok']) {
            return redirect($back)->with('warning', "Sinkron SIMPEG/SIASN untuk {$pegawai->name} gagal atau data tidak ditemukan. Data akun pegawai tetap dapat dipakai.");
        }

        // Data SIMPEG dapat memindahkan pegawai keluar dari cakupan Admin
        if (! $pegawai->fresh()->isInSatkerScopeOf(Auth::user())) {
            return redirect()->route('satker.pengajuan.pilih-pegawai')
                ->with('warning', "Menurut SIMPEG/SIASN, {$pegawai->name} kini di luar cakupan satuan kerja Anda.");
        }

        return redirect($back)
            ->with('success', "Data {$pegawai->name} berhasil disinkronkan dengan SIMPEG/SIASN.")
            ->withInput(['satuan_kerja' => $result['data']['unit_kerja']]);
    }

    private function kirim(FormUsulan $usulan, User $admin): RedirectResponse
    {
        if (! $usulan->isDraft()) {
            return redirect()->route('satker.pengajuan.edit', $usulan)->with('error', 'Gunakan tombol "Kirim ulang" untuk mengirim perbaikan.');
        }

        if ($message = $this->form->missingMessage($usulan)) {
            return redirect()->route('satker.pengajuan.edit', $usulan)->with('error', $message);
        }

        try {
            $this->stateMachine->submit($usulan, $admin);
        } catch (InvalidStatusTransition $e) {
            return redirect()->route('satker.pengajuan.edit', $usulan)->with('error', $e->getMessage());
        }

        return redirect()->route('satker.pengajuan.index')->with('success', "Usulan {$usulan->nama_lengkap} berhasil dikirim dan menunggu verifikasi.");
    }

    private function renderForm(?FormUsulan $usulan, User $pegawai)
    {
        return view('satker.pengajuan.form', [
            'usulan' => $usulan,
            'pegawai' => $pegawai,
            'editable' => $usulan === null || $usulan->isEditableByPegawai(),
        ] + $this->form->viewData($usulan));
    }
}
