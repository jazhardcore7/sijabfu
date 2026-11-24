<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * FR-REG-3: regulasi yang sebelumnya hardcoded dimasukkan sebagai record tanpa file.
     * Idempoten: baris dengan nomor yang sudah ada dilewati.
     */
    public const ROWS = [
        ['kategori' => 'Peraturan Kepala BPS', 'nomor' => 'Perka BPS No. 4 Tahun 2020', 'judul' => 'Petunjuk Pelaksanaan Jabatan Fungsional Statistisi dan Angka Kreditnya', 'tahun' => 2020],
        ['kategori' => 'Peraturan Kepala BPS', 'nomor' => 'Perka BPS No. 7 Tahun 2021', 'judul' => 'Pedoman Penilaian Angka Kredit Jabatan Fungsional Statistisi', 'tahun' => 2021],
        ['kategori' => 'Peraturan Kepala BPS', 'nomor' => 'Perka BPS No. 2 Tahun 2023', 'judul' => 'Standar Kompetensi Jabatan Fungsional di Lingkungan BPS RI', 'tahun' => 2023],
        ['kategori' => 'Peraturan MenPAN-RB', 'nomor' => 'PermenPAN-RB No. 26 Tahun 2016', 'judul' => 'Pengangkatan Pegawai Negeri Sipil dalam Jabatan Fungsional melalui Penyesuaian/Inpassing', 'tahun' => 2016],
        ['kategori' => 'Peraturan MenPAN-RB', 'nomor' => 'PermenPAN-RB No. 13 Tahun 2019', 'judul' => 'Pengusulan, Penetapan, dan Pembinaan Jabatan Fungsional PNS', 'tahun' => 2019],
        ['kategori' => 'Peraturan MenPAN-RB', 'nomor' => 'PermenPAN-RB No. 1 Tahun 2023', 'judul' => 'Jabatan Fungsional Pranata Komputer', 'tahun' => 2023],
        ['kategori' => 'Peraturan BKN', 'nomor' => 'Perka BKN No. 11 Tahun 2022', 'judul' => 'Petunjuk Teknis Pengangkatan dalam Jabatan Fungsional melalui Uji Kompetensi', 'tahun' => 2022],
        ['kategori' => 'Peraturan BKN', 'nomor' => 'Perka BKN No. 6 Tahun 2023', 'judul' => 'Penetapan Standar Angka Kredit Jabatan Fungsional', 'tahun' => 2023],
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::ROWS as $row) {
            if (DB::table('regulasi')->where('nomor', $row['nomor'])->exists()) {
                continue;
            }

            DB::table('regulasi')->insert($row + [
                'file_path' => null,
                'uploaded_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Hanya hapus baris awal yang tidak pernah diberi file.
        DB::table('regulasi')
            ->whereIn('nomor', array_column(self::ROWS, 'nomor'))
            ->whereNull('file_path')
            ->whereNull('uploaded_by')
            ->delete();
    }
};
