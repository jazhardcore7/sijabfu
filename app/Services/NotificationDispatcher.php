<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\FormUsulan;
use App\Models\User;
use App\Notifications\PesanChatBaru;
use App\Notifications\StatusUsulanBerubah;
use App\Notifications\UsulanDikirim;
use Illuminate\Support\Facades\Notification;

/**
 * Menentukan penerima notifikasi database (FR-NOT-1).
 * - Usulan dikirim / dikirim ulang      -> semua user tim_jf aktif
 * - Perubahan status, surat, sertifikat -> Pegawai pemilik usulan
 * - Pesan chat baru                     -> pihak penerima (tim_jf aktif atau Pegawai)
 */
class NotificationDispatcher
{
    public function usulanTransition(string $event, FormUsulan $usulan, ?string $catatan): void
    {
        match ($event) {
            'submit' => Notification::send($this->timJf(), new UsulanDikirim($usulan, false)),
            'resubmit' => Notification::send($this->timJf(), new UsulanDikirim($usulan, true)),
            'decide', 'issueSurat', 'complete', 'rollback' => $this->toOwner($usulan, new StatusUsulanBerubah($usulan, $event, $catatan)),
            default => null,
        };
    }

    public function chatMessage(ChatMessage $message): void
    {
        $message->loadMissing('thread.pegawai', 'sender');
        $thread = $message->thread;

        if ((int) $message->sender_id === (int) $thread->pegawai_id) {
            Notification::send($this->timJf(), new PesanChatBaru($message, route('messenger.show', $thread)));

            return;
        }

        if ($thread->pegawai) {
            $thread->pegawai->notify(new PesanChatBaru($message, route('chat.index')));
        }
    }

    private function toOwner(FormUsulan $usulan, StatusUsulanBerubah $notification): void
    {
        $owner = $usulan->user()->first();
        if ($owner) {
            $owner->notify($notification);
        }
    }

    private function timJf()
    {
        return User::where('role', User::ROLE_TIM_JF)->where('is_active', true)->get();
    }
}
