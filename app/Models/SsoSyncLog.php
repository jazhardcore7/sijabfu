<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Riwayat Sinkronisasi Data SSO (tabel dari Fase 2; proses sinkronisasi di Fase 8).
 */
class SsoSyncLog extends Model
{
    protected $table = 'sso_sync_logs';

    protected $fillable = [
        'triggered_by', 'started_at', 'finished_at',
        'created_count', 'updated_count', 'failed_count', 'satker_missing_count', 'error',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'created_count' => 'integer',
        'updated_count' => 'integer',
        'failed_count' => 'integer',
        'satker_missing_count' => 'integer',
    ];

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
