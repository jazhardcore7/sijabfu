<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProgressBerkas;

class PortalController extends Controller
{
    public function index()
    {
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

    public function progressBerkas()
    {
        $user = Auth::user();
        $berkas = ProgressBerkas::where('user_id', $user->id)->first();
        $tahapan = ProgressBerkas::tahapan();
        $persen = $berkas ? $berkas->progressPersen() : 0;

        return view('modules.progress-berkas', compact('berkas', 'tahapan', 'persen'));
    }

    public function formUsulan()
    {
        $formUrl = 'https://docs.google.com/forms/d/e/1FAIpQLSe-720gJ8ve4Ywhl5z4PURpqgAxtejBiuMieIQrA0PnJt6wfA/viewform';
        $embedUrl = 'https://docs.google.com/forms/d/e/1FAIpQLSe-720gJ8ve4Ywhl5z4PURpqgAxtejBiuMieIQrA0PnJt6wfA/viewform?embedded=true';
        return view('modules.form-usulan', compact('formUrl', 'embedUrl'));
    }

    public function ujiKompetensi()
    {
        $tahapan = [
            [
                'no' => 1,
                'tahap' => 'Pendaftaran',
                'keterangan' => 'Peserta mendaftar melalui portal kompetensi BPS dengan mengisi data diri dan melampirkan dokumen persyaratan.',
            ],
            [
                'no' => 2,
                'tahap' => 'Seleksi Administrasi',
                'keterangan' => 'Verifikasi kelengkapan berkas, termasuk SK terakhir, DP3/SKP 2 tahun terakhir, dan sertifikat pelatihan relevan.',
            ],
            [
                'no' => 3,
                'tahap' => 'Uji Kompetensi Teknis',
                'keterangan' => 'Ujian tertulis atau praktik sesuai bidang jabatan fungsional yang mencakup kompetensi utama dan penunjang.',
            ],
            [
                'no' => 4,
                'tahap' => 'Uji Kompetensi Manajerial',
                'keterangan' => 'Penilaian kemampuan manajerial melalui wawancara, assessment center, atau portofolio kerja.',
            ],
            [
                'no' => 5,
                'tahap' => 'Pengumuman Hasil',
                'keterangan' => 'Hasil uji kompetensi diumumkan secara resmi dan dapat diakses melalui portal kompetensi BPS.',
            ],
        ];

        $syarat = [
            'PNS aktif di lingkungan BPS RI',
            'Memiliki ijazah minimal D-IV atau S1 yang relevan',
            'Masa kerja minimal 1 (satu) tahun sebagai CPNS',
            'Nilai SKP 2 (dua) tahun terakhir minimal "Baik"',
            'Tidak sedang menjalani hukuman disiplin',
            'Surat rekomendasi dari pimpinan unit kerja',
            'Telah mengikuti pelatihan dasar jabatan fungsional yang relevan',
        ];

        return view('modules.uji-kompetensi', compact('tahapan', 'syarat'));
    }

    public function regulasi()
    {
        $regulasi = [
            [
                'kategori' => 'Peraturan Kepala BPS',
                'items' => [
                    [
                        'nomor' => 'Perka BPS No. 4 Tahun 2020',
                        'tentang' => 'Petunjuk Pelaksanaan Jabatan Fungsional Statistisi dan Angka Kreditnya',
                        'tahun' => 2020,
                        'file' => '#',
                    ],
                    [
                        'nomor' => 'Perka BPS No. 7 Tahun 2021',
                        'tentang' => 'Pedoman Penilaian Angka Kredit Jabatan Fungsional Statistisi',
                        'tahun' => 2021,
                        'file' => '#',
                    ],
                    [
                        'nomor' => 'Perka BPS No. 2 Tahun 2023',
                        'tentang' => 'Standar Kompetensi Jabatan Fungsional di Lingkungan BPS RI',
                        'tahun' => 2023,
                        'file' => '#',
                    ],
                ],
            ],
            [
                'kategori' => 'Peraturan Menteri PAN-RB',
                'items' => [
                    [
                        'nomor' => 'PermenPAN-RB No. 26 Tahun 2016',
                        'tentang' => 'Pengangkatan Pegawai Negeri Sipil dalam Jabatan Fungsional melalui Penyesuaian/Inpassing',
                        'tahun' => 2016,
                        'file' => '#',
                    ],
                    [
                        'nomor' => 'PermenPAN-RB No. 13 Tahun 2019',
                        'tentang' => 'Pengusulan, Penetapan, dan Pembinaan Jabatan Fungsional PNS',
                        'tahun' => 2019,
                        'file' => '#',
                    ],
                    [
                        'nomor' => 'PermenPAN-RB No. 1 Tahun 2023',
                        'tentang' => 'Jabatan Fungsional Pranata Komputer',
                        'tahun' => 2023,
                        'file' => '#',
                    ],
                ],
            ],
            [
                'kategori' => 'Peraturan BKN',
                'items' => [
                    [
                        'nomor' => 'Perka BKN No. 11 Tahun 2022',
                        'tentang' => 'Petunjuk Teknis Pengangkatan dalam Jabatan Fungsional melalui Uji Kompetensi',
                        'tahun' => 2022,
                        'file' => '#',
                    ],
                    [
                        'nomor' => 'Perka BKN No. 6 Tahun 2023',
                        'tentang' => 'Penetapan Standar Angka Kredit Jabatan Fungsional',
                        'tahun' => 2023,
                        'file' => '#',
                    ],
                ],
            ],
        ];

        return view('modules.regulasi', compact('regulasi'));
    }

    public function timKerja()
    {
        $tim = [
            [
                'nama' => 'Dr. Ahmad Fauzi, S.ST., M.Si.',
                'nip' => '197805152005011003',
                'jabatan' => 'Ketua Tim Kerja',
                'bidang' => 'Koordinasi & Kebijakan Pembinaan JF',
                'avatar' => 'AF',
                'color' => 'blue',
            ],
            [
                'nama' => 'Sari Dewi Rahayu, S.ST., M.M.',
                'nip' => '198203202008012005',
                'jabatan' => 'Anggota Tim Kerja',
                'bidang' => 'Pengusulan & Penetapan PAK Statistisi',
                'avatar' => 'SR',
                'color' => 'indigo',
            ],
            [
                'nama' => 'Budi Santoso, S.Kom., M.T.',
                'nip' => '198509102010011008',
                'jabatan' => 'Anggota Tim Kerja',
                'bidang' => 'Pembinaan Jabatan Fungsional Pranata Komputer',
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
