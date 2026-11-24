<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\FormUsulan;

class PortalController extends Controller
{
    public function index()
    {
        // Dashboard Tim Pembinaan JF dan Super Admin ada di /admin
        if (Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('landing');
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

    public function timKerja()
    {
        $tim = [
            [
                'nama' => 'Eviphanias H. Sinaga, SST, M.Si.',
                'nip' => '197805152005011003',
                'jabatan' => 'Ketua Tim Pembinaan Jabatan Fungsional',
                'bidang' => 'Biro SDM',
                'avatar' => 'EHS',
                'color' => 'blue',
            ],
            [
                'nama' => 'Sari Dewi Rahayu, S.ST., M.M.',
                'nip' => '198203202008012005',
                'jabatan' => 'Anggota Tim Kerja',
                'bidang' => 'Biro SDM',
                'avatar' => 'SR',
                'color' => 'indigo',
            ],
            [
                'nama' => 'Budi Santoso, S.Kom., M.T.',
                'nip' => '198509102010011008',
                'jabatan' => 'Anggota Tim Kerja',
                'bidang' => 'Biro SDM',
                'avatar' => 'BS',
                'color' => 'orange',
            ],
            [
                'nama' => 'Indah Permata Sari, S.ST.',
                'nip' => '199001152015012004',
                'jabatan' => 'Anggota Tim Kerja',
                'bidang' => 'Uji Kompetensi & Sertifikasi JF',
                'avatar' => 'IP',
                'color' => 'teal',
            ],
            [
                'nama' => 'Rizky Mahendra, S.Si., M.Sc.',
                'nip' => '199205202018011003',
                'jabatan' => 'Anggota Tim Kerja',
                'bidang' => 'Monitoring & Evaluasi Jabatan Fungsional',
                'avatar' => 'RM',
                'color' => 'purple',
            ],
            [
                'nama' => 'Fitri Handayani, A.Md.',
                'nip' => '199510102019022002',
                'jabatan' => 'Staf Administrasi',
                'bidang' => 'Administrasi & Pengarsipan Dokumen JF',
                'avatar' => 'FH',
                'color' => 'rose',
            ],
        ];

        return view('modules.tim-kerja', compact('tim'));
    }
}
