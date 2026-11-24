<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsulanBerkas extends Model
{
    protected $table = 'usulan_berkas';

    public const PEMERIKSAAN_SESUAI = 'sesuai';
    public const PEMERIKSAAN_PERLU_PERBAIKAN = 'perlu_perbaikan';

    protected $fillable = [
        'form_usulan_id', 'jenis_persyaratan', 'file_path', 'original_name',
        'mime', 'size', 'pemeriksaan', 'catatan',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function usulan(): BelongsTo
    {
        return $this->belongsTo(FormUsulan::class, 'form_usulan_id');
    }
}
