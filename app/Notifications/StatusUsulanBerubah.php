<?php

namespace App\Notifications;

use App\Models\FormUsulan;
use Illuminate\Notifications\Notification;

/**
 * Perubahan status usulan untuk Pegawai: keputusan MS/TMS, surat terbit,
 * sertifikat diunggah, atau rollback.
 */
class StatusUsulanBerubah extends Notification
{
    public function __construct(private FormUsulan $usulan, private string $event, private ?string $catatan) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $status = $this->usulan->status_key;

        [$judul, $pesan, $url] = match (true) {
            $this->event === 'decide' && $status === FormUsulan::STATUS_PERBAIKAN => [
                'Usulan perlu perbaikan (TMS)',
                'Berkas usulan Anda dinyatakan TMS. Silakan perbaiki dan kirim ulang.',
                route('pengajuan'),
            ],
            $this->event === 'decide' => [
                'Usulan disetujui (MS)',
                'Berkas usulan Anda dinyatakan Memenuhi Syarat.',
                route('pelacakan-status'),
            ],
            $this->event === 'issueSurat' => [
                'Surat usulan telah terbit',
                'Surat usulan UKOM Anda dapat diunduh di Pelacakan Status.',
                route('pelacakan-status'),
            ],
            $this->event === 'complete' => [
                'Sertifikat UKOM telah diunggah',
                'Sertifikat UKOM Anda dapat diunduh di Pelacakan Status.',
                route('pelacakan-status'),
            ],
            default => [
                'Status usulan diubah',
                'Status usulan Anda dikembalikan ke '.$this->usulan->statusKeyLabel().'.',
                route('pelacakan-status'),
            ],
        };

        return [
            'kategori' => 'status',
            'event' => $this->event,
            'form_usulan_id' => $this->usulan->id,
            'status_key' => $status,
            'judul' => $judul,
            'pesan' => $pesan.($this->catatan ? ' Catatan: '.$this->catatan : ''),
            'url' => $url,
        ];
    }
}
