<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Satu thread per Pegawai dengan Tim Pembinaan JF (inbox bersama, PRD 11.9).
 */
class ChatThread extends Model
{
    protected $table = 'chat_threads';

    protected $fillable = ['pegawai_id', 'last_message_at'];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pegawai_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'thread_id')->orderBy('id');
    }

    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class, 'thread_id')->latestOfMany('id');
    }
}
