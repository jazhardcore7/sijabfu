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

];
