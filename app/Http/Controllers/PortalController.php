<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\FormUsulan;

class PortalController extends Controller
{
    public function index()
    {
        // Beranda dialihkan ke dashboard per role (PRD Revisi bagian 7)
        return redirect()->route('dashboard');
    }

    public function alurLayanan()
    {
        $steps = [
            [
                'number' => '01',
                'title' => 'Verifikasi',
                'description' => 'Tim Kerja Pembinaan JF BPS melakukan verifikasi kelengkapan dan keabsahan dokumen usulan yang diajukan oleh pegawai. Pastikan seluruh persyaratan administrasi telah dipenuhi sebelum berkas diteruskan.',
                'icon' => 'magnifying-glass',
                'color' => 'blue',
            ],
            [
                'number' => '02',
                'title' => 'Akses Surat Usulan Ukom',
                'description' => 'Setelah berkas dinyatakan lengkap, pegawai diberikan surat usulan untuk mengikuti Uji Kompetensi (Ukom). Surat ini menjadi dokumen resmi yang menerangkan bahwa pegawai telah memenuhi syarat administrasi.',
                'icon' => 'document-text',
                'color' => 'indigo',
            ],
            [
                'number' => '03',
                'title' => 'Pengelola',
                'description' => 'Berkas dan surat usulan diproses oleh unit pengelola kepegawaian (Biro SDM / Bagian Kepegawaian) untuk pencatatan resmi, verifikasi internal, dan koordinasi penjadwalan uji kompetensi.',
                'icon' => 'building-office',
                'color' => 'orange',
            ],
            [
                'number' => '04',
                'title' => 'Uji Kompetensi (Ukom)',
                'description' => 'Pegawai mengikuti Uji Kompetensi teknis dan manajerial sesuai jenjang jabatan fungsional yang diusulkan. Ukom diselenggarakan oleh instansi pembina atau lembaga sertifikasi yang ditunjuk.',
                'icon' => 'academic-cap',
                'color' => 'teal',
            ],
            [
                'number' => '05',
                'title' => 'Dibagi ke Instansi Pembina',
                'description' => 'Hasil Uji Kompetensi beserta seluruh berkas dikirimkan ke Instansi Pembina jabatan fungsional terkait (misal: BKN, Kemenkominfo) untuk ditelaah, divalidasi, dan diproses penetapannya.',
                'icon' => 'share',
                'color' => 'purple',
            ],
            [
                'number' => '06',
                'title' => 'Pengumuman',
                'description' => 'Hasil akhir proses pengusulan jabatan fungsional diumumkan secara resmi. Pegawai yang dinyatakan lulus akan menerima Surat Keputusan (SK) Pengangkatan dalam Jabatan Fungsional.',
                'icon' => 'megaphone',
                'color' => 'green',
            ],
        ];

        return view('modules.alur-layanan', compact('steps'));
    }
}
