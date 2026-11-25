<?php

namespace App\Notifications;

use App\Models\FormUsulan;
use App\Models\User;
use Illuminate\Notifications\Notification;

/**
 * Perubahan status usulan: keputusan MS/TMS, surat terbit, sertifikat diunggah, atau rollback.
 * Penerima: Pegawai yang diusulkan dan pengaju usulan (FR-SATKER-16); teks dan tautan
 * menyesuaikan penerima: Admin Satuan Kerja ke halaman usulannya, Pegawai ke Pelacakan Status.
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
        $isAdminSatker = $notifiable instanceof User && $notifiable->isAdminSatker();

        [$judul, $pesan, $url] = $isAdminSatker ? $this->forAdminSatker() : $this->forPegawai();

        return [
            'kategori' => 'status',
            'event' => $this->event,
            'form_usulan_id' => $this->usulan->id,
            'status_key' => $this->usulan->status_key,
            'judul' => $judul,
            'pesan' => $pesan.($this->catatan ? ' Catatan: '.$this->catatan : ''),
            'url' => $url,
        ];
    }

    private function forPegawai(): array
    {
        $status = $this->usulan->status_key;

        return match (true) {
            $this->event === 'decide' && $status === FormUsulan::STATUS_PERBAIKAN && ! $this->usulan->isSelfSubmitted() => [
                'Usulan perlu perbaikan (TMS)',
                'Berkas usulan Anda dinyatakan TMS. Admin Satuan Kerja akan memperbaiki dan mengirim ulang.',
                route('pelacakan-status'),
            ],
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
    }

    private function forAdminSatker(): array
    {
        $status = $this->usulan->status_key;
        $nama = $this->usulan->nama_lengkap;
        $detail = route('satker.pelacakan.show', $this->usulan->id);

        return match (true) {
            $this->event === 'decide' && $status === FormUsulan::STATUS_PERBAIKAN => [
                'Usulan perlu perbaikan (TMS)',
                "Berkas usulan {$nama} dinyatakan TMS. Silakan perbaiki dan kirim ulang.",
                route('satker.pengajuan.edit', $this->usulan->id),
            ],
            $this->event === 'decide' => [
                'Usulan disetujui (MS)',
                "Berkas usulan {$nama} dinyatakan Memenuhi Syarat.",
                $detail,
            ],
            $this->event === 'issueSurat' => [
                'Surat usulan telah terbit',
                "Surat usulan UKOM {$nama} dapat diunduh di Pelacakan Status.",
                $detail,
            ],
            $this->event === 'complete' => [
                'Sertifikat UKOM telah diunggah',
                "Sertifikat UKOM {$nama} dapat diunduh di Pelacakan Status.",
                $detail,
            ],
            default => [
                'Status usulan diubah',
                "Status usulan {$nama} dikembalikan ke ".$this->usulan->statusKeyLabel().'.',
                $detail,
            ],
        };
    }
}
