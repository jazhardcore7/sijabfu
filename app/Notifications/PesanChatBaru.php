<?php

namespace App\Notifications;

use App\Models\ChatMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Pesan chat baru untuk pihak penerima.
 */
class PesanChatBaru extends Notification
{
    public function __construct(private ChatMessage $message, private string $url) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kategori' => 'chat',
            'event' => 'chat',
            'thread_id' => $this->message->thread_id,
            'chat_message_id' => $this->message->id,
            'judul' => 'Pesan baru dari '.($this->message->sender?->name ?? 'pengguna'),
            'pesan' => Str::limit($this->message->body, 120),
            'url' => $this->url,
        ];
    }
}
