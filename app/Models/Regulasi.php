<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Regulasi (PRD Revisi 5.7).
 */
class Regulasi extends Model
{
    use SoftDeletes;

    protected $table = 'regulasi';

    public const KATEGORI_PERKA_BPS = 'Peraturan Kepala BPS';

    public const KATEGORI_MENPAN_RB = 'Peraturan MenPAN-RB';

    public const KATEGORI_BKN = 'Peraturan BKN';

    /** urutan tampil kategori */
    public const KATEGORI = [
        self::KATEGORI_PERKA_BPS,
        self::KATEGORI_MENPAN_RB,
        self::KATEGORI_BKN,
    ];

    protected $fillable = [
        'judul', 'kategori', 'nomor', 'tahun', 'file_path', 'uploaded_by',
    ];

    protected $casts = [
        'tahun' => 'integer',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function hasFile(): bool
    {
        return ! empty($this->file_path);
    }

    /**
     * Pencarian judul atau nomor (FR-REG-1).
     */
    public function scopeSearch($query, ?string $keyword)
    {
        if ($keyword !== null && trim($keyword) !== '') {
            $like = '%'.trim($keyword).'%';
            $query->where(fn ($q) => $q->where('judul', 'like', $like)->orWhere('nomor', 'like', $like));
        }

        return $query;
    }

    /**
     * Nama file unduhan yang aman dari nomor peraturan.
     */
    public function downloadName(): string
    {
        $slug = trim(preg_replace('/[^A-Za-z0-9]+/', '-', $this->nomor), '-');

        return ($slug !== '' ? $slug : 'regulasi-'.$this->id).'.pdf';
    }
}
