<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\FormUsulan;

class AdminController extends Controller
{
    /**
     * Tampilkan Halaman Dashboard Admin Pembinaan Jabatan Fungsional (Attachment 1)
     */
    public function index()
    {
        // 1. Link Internal (Warna Biru / Nuansa BPS)
        $linkInternal = [
            [
                'title' => 'E-TTD BPS',
                'url' => 'https://ettd.bps.go.id',
                'icon' => 'signature',
                'desc' => 'Layanan Tanda Tangan Elektronik Resmi BPS',
            ],
            [
                'title' => 'SIMPEG BPS',
                'url' => 'https://simpeg.bps.go.id',
                'icon' => 'users',
                'desc' => 'Sistem Informasi Manajemen Kepegawaian',
            ],
            [
                'title' => 'PORTAL SDM BPS',
                'url' => 'https://sdm.bps.go.id',
                'icon' => 'building',
                'desc' => 'Layanan Kepegawaian & Biro SDM',
            ],
            [
                'title' => 'E-KINERJA BPS',
                'url' => 'https://kinerja.bps.go.id',
                'icon' => 'chart',
                'desc' => 'Penilaian Kinerja Pegawai',
            ],
            [
                'title' => 'INTRANET BPS',
                'url' => 'https://intranet.bps.go.id',
                'icon' => 'network',
                'desc' => 'Jaringan Portal Internal BPS RI',
            ],
            [
                'title' => 'WEB UTAMA BPS',
                'url' => 'https://www.bps.go.id',
                'icon' => 'globe',
                'desc' => 'Portal Publik Badan Pusat Statistik',
            ],
        ];

        // 2. Link Eksternal (Warna Hijau)
        $linkEksternal = [
            [
                'title' => 'SIASN',
                'url' => 'https://siasn.bkn.go.id',
                'icon' => 'siasn',
                'desc' => 'Sistem Informasi ASN Terintegrasi BKN',
            ],
            [
                'title' => 'BKN',
                'url' => 'https://www.bkn.go.id',
                'icon' => 'bkn',
                'desc' => 'Badan Kepegawaian Negara RI',
            ],
            [
                'title' => 'MYASN BKN',
                'url' => 'https://myasn.bkn.go.id',
                'icon' => 'user-check',
                'desc' => 'Layanan Mandiri Pegawai ASN',
            ],
            [
                'title' => 'KEMENPAN-RB',
                'url' => 'https://www.menpan.go.id',
                'icon' => 'award',
                'desc' => 'Kementerian PAN-RB Republik Indonesia',
            ],
            [
                'title' => 'JDIHN NASIONAL',
                'url' => 'https://jdihn.go.id',
                'icon' => 'book',
                'desc' => 'Jaringan Dokumentasi & Informasi Hukum',
            ],
            [
                'title' => 'PUSAT PEMBINAAN JF',
                'url' => 'https://pusbin.bkn.go.id',
                'icon' => 'shield',
                'desc' => 'Pusat Pembinaan Jabatan Fungsional BKN',
            ],
        ];

        // 3. Link Lainnya (Warna Oranye)
        $linkLainnya = [
            [
                'title' => 'TEMPLATE SURAT',
                'url' => 'https://docs.google.com/document/d/dummy-template-surat-jf/edit',
                'icon' => 'file-text',
                'desc' => 'Format Surat Usulan, Pengantar & Rekomendasi',
            ],
            [
                'title' => 'PERATURAN JF',
                'url' => 'https://jdih.bps.go.id/dummy-peraturan-jf',
                'icon' => 'scale',
                'desc' => 'Kompilasi Regulasi JF & PermenPAN-RB Terkini',
            ],
            [
                'title' => 'PENETAPAN JF',
                'url' => 'https://sijabfu.bps.go.id/dummy-penetapan-jf',
                'icon' => 'badge-check',
                'desc' => 'Berkas dan SK Penetapan Jabatan Fungsional',
            ],
            [
                'title' => 'REKOMENDASI JF',
                'url' => 'https://sijabfu.bps.go.id/dummy-rekomendasi-jf',
                'icon' => 'target',
                'desc' => 'Dokumen Rekomendasi Formasi & Pengangkatan',
            ],
            [
                'title' => 'ANALISIS BEBAN KERJA',
                'url' => 'https://sijabfu.bps.go.id/dummy-abk-jf',
                'icon' => 'activity',
                'desc' => 'Kalkulator & Dokumen ABK JF Satker',
            ],
            [
                'title' => 'MATRIKS KOMPETENSI',
                'url' => 'https://sijabfu.bps.go.id/dummy-matriks-kompetensi',
                'icon' => 'grid',
                'desc' => 'Standar Kompetensi Jenjang JF BPS',
            ],
        ];

        // 4. Akses Cepat (Bottom Bar)
        $aksesCepat = [
            'form_usulan' => route('form-usulan'),
            'response' => 'https://docs.google.com/spreadsheets/d/1dummy-response-sijabfu-bps/edit',
        ];

        // 5. Data Pengajuan Usulan Pegawai
        $daftarUsulan = FormUsulan::with('user')->latest()->get();
        $tahapan = FormUsulan::tahapan();

        return view('admin.dashboard', compact('linkInternal', 'linkEksternal', 'linkLainnya', 'aksesCepat', 'daftarUsulan', 'tahapan'));
    }

    /**
     * Halaman Admin Panel Khusus Monitoring Progress Usulan
     */
    public function monitoring(Request $request)
    {
        $query = FormUsulan::with('user');

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter pencarian
        if ($request->filled('q')) {
            $keyword = '%' . $request->q . '%';
            $query->where(function($q) use ($keyword) {
                $q->where('nama_lengkap', 'like', $keyword)
                  ->orWhere('nip', 'like', $keyword)
                  ->orWhere('satuan_kerja', 'like', $keyword)
                  ->orWhere('jabatan_fungsional', 'like', $keyword)
                  ->orWhere('nomor_surat', 'like', $keyword);
            });
        }

        $daftarUsulan = $query->latest()->paginate(10)->withQueryString();
        $tahapan = FormUsulan::tahapan();

        // Statistik ringkasan
        $stats = [
            'total'   => FormUsulan::count(),
            'tahap_1' => FormUsulan::where('status', 1)->count(),
            'tahap_2' => FormUsulan::where('status', 2)->count(),
            'tahap_3' => FormUsulan::where('status', 3)->count(),
            'tahap_4' => FormUsulan::where('status', 4)->count(),
            'tahap_5' => FormUsulan::where('status', 5)->count(),
        ];

        return view('admin.monitoring', compact('daftarUsulan', 'tahapan', 'stats'));
    }

    /**
     * Update Status Usulan oleh Admin
     */
    public function updateStatus(Request $request, $id)
    {
        $usulan = FormUsulan::findOrFail($id);

        Gate::authorize('updateStatus', $usulan);

        $validated = $request->validate([
            'status' => 'required|integer|min:1|max:5',
            'catatan' => 'nullable|string|max:1000',
        ]);

        $usulan->update([
            'status' => $validated['status'],
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return redirect()->back()->with('success', "Status usulan untuk {$usulan->nama_lengkap} berhasil diperbarui menjadi Tahap {$usulan->status}.");
    }
}
