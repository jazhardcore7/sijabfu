<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Chat dua arah Pegawai <-> Tim Pembinaan JF (PRD Revisi 5.8).
 *
 * Status baca:
 * - Pesan dari staf dianggap terbaca saat Pegawai membuka/polling thread-nya.
 * - Pesan dari Pegawai dianggap terbaca untuk seluruh tim saat staf mana pun membuka thread (inbox bersama).
 */
class ChatService
{
    public function threadFor(User $pegawai): ?ChatThread
    {
        return ChatThread::where('pegawai_id', $pegawai->id)->first();
    }

    public function send(User $sender, ?ChatThread $thread, string $body): ChatMessage
    {
        return DB::transaction(function () use ($sender, $thread, $body) {
            $thread ??= ChatThread::firstOrCreate(['pegawai_id' => $sender->id]);

            $message = ChatMessage::create([
                'thread_id' => $thread->id,
                'sender_id' => $sender->id,
                'body' => $body,
            ]);

            $thread->forceFill(['last_message_at' => $message->created_at])->save();

            return $message;
        });
    }

    /**
     * Tandai pesan dari pihak lain sebagai terbaca, sesuai siapa yang membuka.
     */
    public function markRead(ChatThread $thread, User $viewer): void
    {
        $query = ChatMessage::where('thread_id', $thread->id)->whereNull('read_at');

        if ($viewer->isPegawai()) {
            $query->where('sender_id', '!=', $viewer->id);
        } else {
            $query->where('sender_id', $thread->pegawai_id);
        }

        $query->update(['read_at' => now()]);
    }

    /**
     * Jumlah pesan belum terbaca untuk badge sidebar.
     */
    public function unreadCount(User $user): int
    {
        if ($user->isPegawai()) {
            $thread = $this->threadFor($user);

            return $thread
                ? ChatMessage::where('thread_id', $thread->id)->whereNull('read_at')->where('sender_id', '!=', $user->id)->count()
                : 0;
        }

        // Staf: pesan dari Pegawai yang belum dibaca siapa pun di tim (inbox bersama)
        return ChatMessage::join('chat_threads', 'chat_threads.id', '=', 'chat_messages.thread_id')
            ->whereNull('chat_messages.read_at')
            ->whereColumn('chat_messages.sender_id', 'chat_threads.pegawai_id')
            ->count();
    }

    /**
     * Pesan setelah id tertentu (polling).
     */
    public function messagesAfter(ChatThread $thread, int $afterId)
    {
        return $thread->messages()->with('sender')->where('id', '>', $afterId)->get();
    }

    /**
     * Daftar thread untuk inbox staf, terbaru di atas, dengan jumlah belum terbaca.
     */
    public function inbox()
    {
        return ChatThread::with(['pegawai', 'latestMessage'])
            ->whereNotNull('last_message_at')
            ->withCount(['messages as unread_count' => function ($q) {
                $q->whereNull('read_at')->whereColumn('chat_messages.sender_id', 'chat_threads.pegawai_id');
            }])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->get();
    }
}
