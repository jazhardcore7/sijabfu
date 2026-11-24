<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidStatusTransition;
use App\Models\FormUsulan;
use App\Models\UsulanBerkas;
use App\Services\UsulanStateMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifikasi Berkas Usulan Pegawai (PRD Revisi 5.5), untuk Tim Pembinaan JF dan Super Admin.
 */
class VerifikasiController extends Controller
{
    public function __construct(private UsulanStateMachine $stateMachine)
    {
    }

    /**
     * FR-VB-1: daftar pegawai dengan usulan, filter status, cari NIP/nama, 15 per halaman.
     */
    public function index(Request $request)
    {
        $daftarUsulan = FormUsulan::submitted()
            ->with('user')
            ->filterStaf($request->query('status'), $request->query('q'))
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('verifikasi.index', [
            'daftarUsulan' => $daftarUsulan,
            'statusLabels' => FormUsulan::STATUS_LABELS,
        ]);
    }

    /**
     * FR-VB-2: data usulan dan daftar berkas dengan preview/unduh.
     */
    public function show(FormUsulan $usulan)
    {
        abort_if($usulan->isDraft(), 404);

        return $this->renderShow($usulan);
    }

    /**
     * aksi=periksa (FR-VB-3) atau aksi=keputusan (FR-VB-4, FR-VB-5).
     */
    public function update(Request $request, FormUsulan $usulan): Response
    {
        abort_if($usulan->isDraft(), 404);

        $request->validate(['aksi' => ['required', Rule::in(['periksa', 'keputusan'])]]);

        return $request->input('aksi') === 'periksa'
            ? $this->periksa($request, $usulan)
            : $this->keputusan($request, $usulan);
    }

    private function periksa(Request $request, FormUsulan $usulan): Response
    {
        if ($usulan->status_key !== FormUsulan::STATUS_VERIFIKASI) {
            return $this->renderShow($usulan, 'Pemeriksaan berkas hanya dapat dilakukan saat status Verifikasi.', 422);
        }

        $validated = $request->validate([
            'pemeriksaan' => ['required', 'array'],
            'pemeriksaan.*.hasil' => ['nullable', Rule::in([UsulanBerkas::PEMERIKSAAN_SESUAI, UsulanBerkas::PEMERIKSAAN_PERLU_PERBAIKAN])],
            'pemeriksaan.*.catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $berkasList = $usulan->berkas()->get()->keyBy('id');

        DB::transaction(function () use ($validated, $berkasList) {
            foreach ($validated['pemeriksaan'] as $berkasId => $input) {
                $berkas = $berkasList->get((int) $berkasId);
                if (!$berkas) {
                    continue; // berkas milik usulan lain diabaikan
                }

                Gate::authorize('periksa', $berkas);

                $catatan = trim((string) ($input['catatan'] ?? ''));
                $berkas->update([
                    'pemeriksaan' => $input['hasil'] ?? null,
                    'catatan' => $catatan === '' ? null : $catatan,
                ]);
            }
        });

        return redirect()->route('verifikasi.show', $usulan)->with('success', 'Hasil pemeriksaan berkas berhasil disimpan.');
    }

    private function keputusan(Request $request, FormUsulan $usulan): Response
    {
        $validated = $request->validate([
            'hasil' => ['required', Rule::in([FormUsulan::HASIL_MS, FormUsulan::HASIL_TMS])],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'hasil.required' => 'Pilih keputusan MS atau TMS.',
        ]);

        try {
            $this->stateMachine->decide($usulan, Auth::user(), $validated['hasil'], $validated['catatan'] ?? null);
        } catch (InvalidStatusTransition $e) {
            return $this->renderShow($usulan->fresh(), $e->getMessage(), 422);
        }

        $label = $validated['hasil'] === FormUsulan::HASIL_MS ? 'MS (Disetujui)' : 'TMS (Perbaikan)';

        return redirect()->route('verifikasi.show', $usulan)->with('success', "Keputusan {$label} untuk {$usulan->nama_lengkap} berhasil disimpan.");
    }

    private function renderShow(FormUsulan $usulan, ?string $error = null, int $status = 200): Response
    {
        return response()->view('verifikasi.show', [
            'usulan' => $usulan,
            'berkas' => $usulan->berkas()->get()->keyBy('jenis_persyaratan'),
            'persyaratan' => config('sijabfu.berkas_persyaratan'),
            'logs' => $usulan->statusLogs()->with('actor')->get(),
            'transitionError' => $error,
        ], $status);
    }
}
