<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $table = 'chat_messages';

    public const MAX_LENGTH = 2000;

    protected $fillable = ['thread_id', 'sender_id', 'body', 'read_at'];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(ChatThread::class, 'thread_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Data untuk polling JSON. body dikirim apa adanya dan dirender dengan textContent di klien.
     */
    public function toChatArray(User $viewer): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'mine' => (int) $this->sender_id === (int) $viewer->id,
            'sender' => $this->sender?->name ?? 'Pengguna',
            'sender_level' => $this->sender?->levelLabel() ?? '',
            'time' => $this->created_at?->format('d M Y H:i'),
        ];
    }
}
