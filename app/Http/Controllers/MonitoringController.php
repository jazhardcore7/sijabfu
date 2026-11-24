<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidStatusTransition;
use App\Models\FormUsulan;
use App\Services\UsulanStateMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

/**
 * Monitoring Progress UKOM (PRD Revisi 5.6), untuk Tim Pembinaan JF dan Super Admin.
 * Semua perubahan status lewat UsulanStateMachine (FR-MON-2, FR-MON-3).
 */
class MonitoringController extends Controller
{
    public function __construct(private UsulanStateMachine $stateMachine)
    {
    }

    /**
     * FR-MON-1: progres per pegawai, jumlah per status (filter), cari, 15 per halaman.
     */
    public function index(Request $request)
    {
        return $this->renderIndex($request);
    }

    /**
     * aksi=tetapkan (surat usulan), aksi=selesaikan (sertifikat UKOM), aksi=rollback (Super Admin).
     */
    public function updateStatus(Request $request, FormUsulan $usulan): Response
    {
        abort_if($usulan->isDraft(), 404);

        $maxKb = config('sijabfu.berkas_max_kb');
        $pdfRule = ['nullable', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:' . $maxKb];

        $validated = $request->validate([
            'aksi' => ['required', Rule::in(['tetapkan', 'selesaikan', 'rollback'])],
            'surat' => $pdfRule,
            'sertifikat' => $pdfRule,
            'hasil_ukom' => ['nullable', 'string', 'max:1000'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'ke_status' => ['nullable', 'string'],
            'alasan' => ['nullable', 'string', 'max:1000'],
        ], [
            'surat.mimes' => 'Surat usulan harus berformat PDF.',
            'surat.mimetypes' => 'Surat usulan harus berformat PDF.',
            'surat.max' => 'Ukuran surat usulan maksimal ' . ($maxKb / 1024) . ' MB.',
            'sertifikat.mimes' => 'Sertifikat UKOM harus berformat PDF.',
            'sertifikat.mimetypes' => 'Sertifikat UKOM harus berformat PDF.',
            'sertifikat.max' => 'Ukuran sertifikat UKOM maksimal ' . ($maxKb / 1024) . ' MB.',
        ]);

        $actor = Auth::user();
        $disk = Storage::disk(config('sijabfu.disk'));
        $newPath = null;
        $oldPath = null;

        try {
            switch ($validated['aksi']) {
                case 'tetapkan':
                    $this->assertStatus($usulan, FormUsulan::STATUS_DISETUJUI, FormUsulan::STATUS_DITETAPKAN);
                    $oldPath = $usulan->surat_usulan_path;
                    $newPath = $request->hasFile('surat') ? $disk->putFile('usulan/' . $usulan->id, $request->file('surat')) : null;
                    $this->stateMachine->issueSurat($usulan, $actor, $newPath, $validated['catatan'] ?? null);
                    $message = 'Surat usulan diterbitkan. Status: Ditetapkan.';
                    break;

                case 'selesaikan':
                    $this->assertStatus($usulan, FormUsulan::STATUS_DITETAPKAN, FormUsulan::STATUS_SELESAI);
                    $oldPath = $usulan->sertifikat_path;
                    $newPath = $request->hasFile('sertifikat') ? $disk->putFile('usulan/' . $usulan->id, $request->file('sertifikat')) : null;
                    $this->stateMachine->complete($usulan, $actor, $newPath, $validated['hasil_ukom'] ?? null, $validated['catatan'] ?? null);
                    $message = 'Sertifikat UKOM diunggah. Status: Selesai.';
                    break;

                default: // rollback (AuthorizationException untuk non Super Admin -> 403)
                    $this->stateMachine->rollback($usulan, $actor, (string) ($validated['ke_status'] ?? ''), $validated['alasan'] ?? null);
                    $message = 'Status dikembalikan ke ' . $usulan->statusKeyLabel() . '.';
                    break;
            }
        } catch (InvalidStatusTransition $e) {
            if ($newPath) {
                $disk->delete($newPath);
            }

            return $this->renderIndex($request, $e->getMessage(), 422);
        }

        if ($oldPath && $newPath && $oldPath !== $newPath) {
            $disk->delete($oldPath);
        }

        return redirect()->route('monitoring.index', array_filter([
            'status' => $request->query('status'),
            'q' => $request->query('q'),
        ]))->with('success', "{$usulan->nama_lengkap}: {$message}");
    }

    /**
     * Cek status sebelum menyimpan file, agar file tidak tersimpan untuk transisi yang ditolak.
     */
    private function assertStatus(FormUsulan $usulan, string $expected, string $to): void
    {
        if ($usulan->status_key !== $expected) {
            throw InvalidStatusTransition::from($usulan->status_key, $to);
        }
    }

    private function renderIndex(Request $request, ?string $error = null, int $status = 200): Response
    {
        $statusFilter = $request->query('status');
        $keyword = $request->query('q');

        $daftarUsulan = FormUsulan::submitted()
            ->with('user')
            ->filterStaf($statusFilter, $keyword)
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $counts = FormUsulan::submitted()
            ->selectRaw('status_key, count(*) as jumlah')
            ->groupBy('status_key')
            ->pluck('jumlah', 'status_key');

        $stats = ['total' => (int) $counts->sum()];
        foreach (array_keys(FormUsulan::STATUS_LABELS) as $key) {
            $stats[$key] = (int) ($counts[$key] ?? 0);
        }

        return response()->view('admin.monitoring', [
            'daftarUsulan' => $daftarUsulan,
            'stats' => $stats,
            'statusLabels' => FormUsulan::STATUS_LABELS,
            'transitionError' => $error,
            'isSuperAdmin' => Auth::user()->isSuperAdmin(),
        ], $status);
    }
}
