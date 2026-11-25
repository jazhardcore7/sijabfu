<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Berkas persyaratan usulan (FR-PU-3)
    |--------------------------------------------------------------------------
    | [ASSUMPTION PRD 11.2] Daftar 8 dokumen wajib dari
    | docs/DOKUMENTASI_PROSES_BISNIS_SIJABFU.md bagian 4.3.
    | Kunci dipakai sebagai usulan_berkas.jenis_persyaratan.
    */
    'berkas_persyaratan' => [
        'sk_cpns_pns' => 'SK CPNS / PNS',
        'ijazah_transkrip' => 'Ijazah dan Transkrip Nilai',
        'skp' => 'Penilaian Kinerja Pegawai (SKP) 2 Tahun Terakhir',
        'identitas' => 'KTP / Kartu Identitas Pegawai BPS',
        'surat_rekomendasi' => 'Surat Rekomendasi Pimpinan Unit Kerja',
        'sertifikat_pelatihan' => 'Sertifikat Pelatihan JF Relevan',
        'pas_foto' => 'Pas Foto Formal Terbaru 4x6',
        'dupak' => 'DUPAK / Bukti Capaian Angka Kredit Terakhir',
    ],

    /*
    | [ASSUMPTION PRD 11.8] PDF saja, maksimal 5 MB per berkas (dalam kilobyte).
    */
    'berkas_max_kb' => 5120,

    /*
    | [ASSUMPTION PRD 11.8] File regulasi: PDF saja, maksimal 10 MB (dalam kilobyte).
    */
    'regulasi_max_kb' => 10240,

    /*
    | Disk privat untuk semua file usulan (root storage/app/private, tidak di-link ke public).
    */
    'disk' => 'local',

    /*
    |--------------------------------------------------------------------------
    | SSO dan SIMPEG/SIASN (FR-AUTH-1, FR-PU-1, FR-USR-2, FR-USR-3)
    |--------------------------------------------------------------------------
    | [ASSUMPTION PRD 11.1] Protokol dan endpoint asli belum diketahui; hanya
    | driver "fake" yang tersedia. Driver fake ditolak saat APP_ENV=production.
    */
    'sso' => [
        'driver' => env('SSO_DRIVER', 'fake'),
    ],

    'simpeg' => [
        'driver' => env('SIMPEG_DRIVER', 'fake'),
    ],

    /*
    | FR-AUTH-3: login lokal NIP/password. Default aktif kecuali di production.
    */
    'auth_local_enabled' => (bool) env('AUTH_LOCAL_ENABLED', env('APP_ENV', 'production') !== 'production'),

    /*
    |--------------------------------------------------------------------------
    | Kode provinsi BPS (FR-SATKER-3) [ASSUMPTION keputusan 12]
    |--------------------------------------------------------------------------
    | Dipakai untuk label filter dan validasi provinsi_kode (2 digit).
    */
    'provinsi' => [
        '11' => 'Aceh', '12' => 'Sumatera Utara', '13' => 'Sumatera Barat', '14' => 'Riau',
        '15' => 'Jambi', '16' => 'Sumatera Selatan', '17' => 'Bengkulu', '18' => 'Lampung',
        '19' => 'Kepulauan Bangka Belitung', '21' => 'Kepulauan Riau', '31' => 'DKI Jakarta',
        '32' => 'Jawa Barat', '33' => 'Jawa Tengah', '34' => 'DI Yogyakarta', '35' => 'Jawa Timur',
        '36' => 'Banten', '51' => 'Bali', '52' => 'Nusa Tenggara Barat', '53' => 'Nusa Tenggara Timur',
        '61' => 'Kalimantan Barat', '62' => 'Kalimantan Tengah', '63' => 'Kalimantan Selatan',
        '64' => 'Kalimantan Timur', '65' => 'Kalimantan Utara', '71' => 'Sulawesi Utara',
        '72' => 'Sulawesi Tengah', '73' => 'Sulawesi Selatan', '74' => 'Sulawesi Tenggara',
        '75' => 'Gorontalo', '76' => 'Sulawesi Barat', '81' => 'Maluku', '82' => 'Maluku Utara',
        '91' => 'Papua Barat', '92' => 'Papua Barat Daya', '94' => 'Papua', '95' => 'Papua Selatan',
        '96' => 'Papua Tengah', '97' => 'Papua Pegunungan',
    ],

];
