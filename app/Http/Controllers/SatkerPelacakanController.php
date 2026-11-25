<?php

namespace App\Http\Controllers;

use App\Models\FormUsulan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * Pelacakan Status untuk Admin Satuan Kerja Provinsi (FR-SATKER-14):
 * usulan terkirim yang diajukan Admin ini, filter status, cari NIP/nama, 15 per halaman, dan detail tracker.
 */
class SatkerPelacakanController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->query('q', ''));

        $daftar = FormUsulan::where('submitted_by', Auth::id())
            ->submitted()
            ->filterStaf($request->query('status'), null)
            ->when($keyword !== '', function ($q) use ($keyword) {
                $like = '%'.$keyword.'%';
                $q->where(fn ($w) => $w->where('nip', 'like', $like)->orWhere('nama_lengkap', 'like', $like));
            })
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $daftar->getCollection()->each(fn (FormUsulan $u) => $u->setAttribute('persen', PelacakanController::persen($u)));

        return view('satker.pelacakan.index', [
            'daftarUsulan' => $daftar,
            'statusLabels' => FormUsulan::STATUS_LABELS,
        ]);
    }

    public function show(FormUsulan $usulan)
    {
        Gate::authorize('manageAsSatker', $usulan);
        abort_if($usulan->isDraft(), 404);

        $perbaikanUrl = $usulan->status_key === FormUsulan::STATUS_PERBAIKAN ? route('satker.pengajuan.edit', $usulan) : null;

        return view('satker.pelacakan.show', PelacakanController::detailData($usulan, $perbaikanUrl));
    }
}
