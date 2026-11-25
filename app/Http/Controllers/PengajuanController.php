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
use Illuminate\Support\Facades\Gate;

/**
 * Modul Pengajuan Usulan oleh Pegawai (PRD Revisi 5.3).
 * Akses route dibatasi `can:create` (FR-SATKER-6): Pegawai Pusat, atau Pegawai yang masih
 * punya usulan aktif yang diajukannya sendiri (keputusan 11).
 */
class PengajuanController extends Controller
{
    public function __construct(
        private UsulanStateMachine $stateMachine,
        private UsulanFormService $form,
    ) {}

    public function index()
    {
        $user = Auth::user();
        $usulan = FormUsulan::activeFor($user);
        $latest = $this->latestUsulan($user);

        return view('modules.form-usulan', [
            'usulan' => $usulan,
            'editable' => $this->canEdit($usulan, $user),
            'usulanSelesai' => $usulan === null && $latest?->status_key === FormUsulan::STATUS_SELESAI ? $latest : null,
        ] + $this->form->viewData($usulan));
    }

    /**
     * Simpan entri data dan berkas (aksi=simpan), atau simpan lalu kirim (aksi=kirim).
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', FormUsulan::class);

        $user = Auth::user();
        $usulan = FormUsulan::activeFor($user);

        // FR-PU-5 / FR-PU-6: hanya draf atau perbaikan milik pengaju yang boleh diubah
        if ($usulan && ! $this->canEdit($usulan, $user)) {
            return redirect()->route('pengajuan')->with('error', 'Usulan Anda sedang diproses dan tidak dapat diubah.');
        }

        [$validated, $uploads] = $this->form->validate($request);
        $usulan = $this->form->save($usulan, $user, $user, $validated, $uploads);

        if (($validated['aksi'] ?? 'simpan') === 'kirim') {
            return $this->kirim($usulan, $user);
        }

        return redirect()->route('pengajuan')->with('success', 'Data dan berkas usulan berhasil disimpan.');
    }

    /**
     * FR-PU-1: Sinkron data dengan SIMPEG/SIASN. Gagal -> peringatan, form tetap dapat dipakai.
     */
    public function sinkron(SimpegSyncService $simpeg): RedirectResponse
    {
        $result = $simpeg->syncUser(Auth::user());

        if (! $result['ok']) {
            return redirect()->route('pengajuan')->with('warning', $result['message']);
        }

        // Satuan Kerja di form diisi dari unit kerja SIMPEG/SIASN
        return redirect()->route('pengajuan')
            ->with('success', $result['message'])
            ->withInput(['satuan_kerja' => $result['data']['unit_kerja']]);
    }

    /**
     * Kirim ulang usulan dari status perbaikan (FR-PU-5, FR-SATKER-11).
     */
    public function kirimUlang(): RedirectResponse
    {
        $user = Auth::user();
        $usulan = FormUsulan::activeFor($user);

        if (! $usulan || $usulan->status_key !== FormUsulan::STATUS_PERBAIKAN || ! $usulan->isSelfSubmitted()) {
            return redirect()->route('pengajuan')->with('error', 'Kirim ulang hanya dapat dilakukan saat status Perbaikan.');
        }

        if ($message = $this->form->missingMessage($usulan)) {
            return redirect()->route('pengajuan')->with('error', $message);
        }

        try {
            $this->stateMachine->resubmit($usulan, $user);
        } catch (InvalidStatusTransition $e) {
            return redirect()->route('pengajuan')->with('error', $e->getMessage());
        }

        return redirect()->route('pelacakan-status')->with('success', 'Usulan berhasil dikirim ulang dan menunggu verifikasi.');
    }

    /**
     * FR-PU-4: kirim hanya bila semua slot berkas sudah terisi.
     */
    private function kirim(FormUsulan $usulan, User $user): RedirectResponse
    {
        if (! $usulan->isDraft()) {
            return redirect()->route('pengajuan')->with('error', 'Gunakan tombol "Kirim ulang" untuk mengirim perbaikan.');
        }

        if ($message = $this->form->missingMessage($usulan)) {
            return redirect()->route('pengajuan')->with('error', $message);
        }

        try {
            $this->stateMachine->submit($usulan, $user);
        } catch (InvalidStatusTransition $e) {
            return redirect()->route('pengajuan')->with('error', $e->getMessage());
        }

        return redirect()->route('pelacakan-status')->with('success', 'Usulan berhasil dikirim! Pantau statusnya di halaman ini.');
    }

    /**
     * Usulan baru, atau draf/perbaikan yang diajukan sendiri oleh Pegawai ini.
     * Usulan yang diajukan Admin Satuan Kerja hanya dapat dilihat (FR-SATKER-11).
     */
    private function canEdit(?FormUsulan $usulan, User $user): bool
    {
        return $usulan === null || ($usulan->isEditableByPegawai() && $usulan->isSelfSubmitted());
    }

    private function latestUsulan(User $user): ?FormUsulan
    {
        return FormUsulan::where('user_id', $user->id)->orderByDesc('id')->first();
    }
}
