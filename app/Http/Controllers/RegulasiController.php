<?php

namespace App\Http\Controllers;

use App\Models\Regulasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Regulasi untuk semua role: daftar per kategori, cari, preview, unduh (FR-REG-1).
 */
class RegulasiController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->query('q');
        $records = Regulasi::search($keyword)->orderByDesc('tahun')->orderBy('nomor')->get();

        $regulasi = [];
        foreach (Regulasi::KATEGORI as $kategori) {
            $regulasi[] = [
                'kategori' => $kategori,
                'items' => $records->where('kategori', $kategori)->values(),
            ];
        }

        return view('modules.regulasi', [
            'regulasi' => $regulasi,
            'keyword' => $keyword,
            'total' => $records->count(),
        ]);
    }

    public function preview(Regulasi $regulasi): Response
    {
        return $this->serve($regulasi, 'inline');
    }

    public function download(Regulasi $regulasi): Response
    {
        return $this->serve($regulasi, 'attachment');
    }

    private function serve(Regulasi $regulasi, string $disposition): Response
    {
        Gate::authorize('view', $regulasi);

        $disk = Storage::disk(config('sijabfu.disk'));
        abort_if(!$regulasi->hasFile() || !$disk->exists($regulasi->file_path), 404, 'File belum tersedia.');

        $headers = ['Content-Type' => 'application/pdf', 'X-Content-Type-Options' => 'nosniff'];

        return $disposition === 'inline'
            ? $disk->response($regulasi->file_path, $regulasi->downloadName(), $headers, 'inline')
            : $disk->download($regulasi->file_path, $regulasi->downloadName(), $headers);
    }
}
