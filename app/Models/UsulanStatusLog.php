<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsulanStatusLog extends Model
{
    protected $table = 'usulan_status_logs';

    protected $fillable = [
        'form_usulan_id', 'actor_id', 'from_status', 'to_status', 'catatan',
    ];

    public function usulan(): BelongsTo
    {
        return $this->belongsTo(FormUsulan::class, 'form_usulan_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
