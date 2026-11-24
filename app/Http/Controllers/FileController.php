<?php

namespace App\Http\Controllers;

use App\Models\FormUsulan;
use App\Models\UsulanBerkas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Akses file usulan melalui route yang diotorisasi (FR-PS-6).
 * ?mode=preview  -> Content-Disposition inline
 * ?mode=download -> Content-Disposition attachment (default)
 */
class FileController extends Controller
{
    public function show(Request $request, string $type, int $id): Response
    {
        [$usulan, $path, $name] = match ($type) {
            'berkas' => $this->berkas($id),
            'surat' => $this->usulanFile($id, 'surat_usulan_path', 'surat-usulan'),
            'sertifikat' => $this->usulanFile($id, 'sertifikat_path', 'sertifikat-ukom'),
        };

        // Pemilik, Tim Pembinaan JF, atau Super Admin (FormUsulanPolicy::view)
        Gate::authorize('view', $usulan);

        // Pegawai hanya dapat membuka surat/sertifikat pada status yang membukanya (FR-PS-4, FR-PS-5)
        $user = Auth::user();
        if (! $user->isAdmin()) {
            $allowed = match ($type) {
                'surat' => [FormUsulan::STATUS_DITETAPKAN, FormUsulan::STATUS_SELESAI],
                'sertifikat' => [FormUsulan::STATUS_SELESAI],
                default => null,
            };
            abort_if($allowed !== null && ! in_array($usulan->status_key, $allowed, true), 403);
        }

        $disk = Storage::disk(config('sijabfu.disk'));
        abort_if(! $path || ! $disk->exists($path), 404, 'Dokumen belum diunggah.');

        $headers = ['Content-Type' => 'application/pdf', 'X-Content-Type-Options' => 'nosniff'];

        return $request->query('mode') === 'preview'
            ? $disk->response($path, $name, $headers, 'inline')
            : $disk->download($path, $name, $headers);
    }

    private function berkas(int $id): array
    {
        $berkas = UsulanBerkas::with('usulan')->findOrFail($id);

        return [$berkas->usulan, $berkas->file_path, $berkas->original_name];
    }

    private function usulanFile(int $id, string $column, string $prefix): array
    {
        $usulan = FormUsulan::findOrFail($id);

        return [$usulan, $usulan->{$column}, $prefix.'-'.$usulan->nip.'.pdf'];
    }
}
