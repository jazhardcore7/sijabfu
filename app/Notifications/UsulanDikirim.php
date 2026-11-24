<?php

namespace App\Notifications;

use App\Models\FormUsulan;
use Illuminate\Notifications\Notification;

/**
 * Usulan baru atau dikirim ulang (untuk Tim Pembinaan JF).
 */
class UsulanDikirim extends Notification
{
    public function __construct(private FormUsulan $usulan, private bool $ulang) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kategori' => 'usulan',
            'event' => $this->ulang ? 'resubmit' : 'submit',
            'form_usulan_id' => $this->usulan->id,
            'judul' => $this->ulang ? 'Usulan dikirim ulang' : 'Usulan baru masuk',
            'pesan' => $this->usulan->nama_lengkap.' ('.$this->usulan->jabatan_fungsional.') '
                .($this->ulang ? 'mengirim ulang usulan setelah perbaikan.' : 'mengirim usulan baru.'),
            'url' => route('verifikasi.show', $this->usulan->id),
        ];
    }
}
