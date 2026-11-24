<?php

namespace App\Services;

use App\Exceptions\InvalidStatusTransition;
use App\Models\FormUsulan;
use App\Models\User;
use App\Models\UsulanBerkas;
use App\Models\UsulanStatusLog;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

/**
 * State machine siklus hidup usulan (PRD Revisi bagian 4).
 *
 * Setiap perpindahan berjalan dalam satu transaksi dan menulis satu baris
 * usulan_status_logs (FR-MON-3). Perpindahan yang tidak diizinkan melempar
 * InvalidStatusTransition dan tidak mengubah apa pun (FR-MON-2).
 * Role yang tidak berhak melempar AuthorizationException.
 */
class UsulanStateMachine
{
    public function __construct(private ?NotificationDispatcher $notifications = null)
    {
        $this->notifications ??= app(NotificationDispatcher::class);
    }

    /** urutan maju, dipakai untuk menentukan rollback (mundur) */
    private const RANK = [
        FormUsulan::STATUS_VERIFIKASI => 1,
        FormUsulan::STATUS_PERBAIKAN => 1,
        FormUsulan::STATUS_DISETUJUI => 2,
        FormUsulan::STATUS_DITETAPKAN => 3,
        FormUsulan::STATUS_SELESAI => 4,
    ];

    /**
     * 1. Usulan baru -> verifikasi (oleh Pegawai pemilik).
     */
    public function submit(FormUsulan $usulan, User $actor): FormUsulan
    {
        $this->assertOwner($usulan, $actor);

        return $this->transition($usulan, $actor, null, FormUsulan::STATUS_VERIFIKASI, null, [
            'submitted_at' => now(),
        ], 'submit');
    }

    /**
     * 2 dan 3. Keputusan verifikasi: MS -> disetujui, TMS -> perbaikan.
     * TMS wajib punya alasan: catatan, atau minimal satu berkas bertanda perlu perbaikan
     * (default untuk konflik PRD bagian 4 vs FR-VB-4, menunggu konfirmasi).
     */
    public function decide(FormUsulan $usulan, User $actor, string $hasil, ?string $catatan = null): FormUsulan
    {
        $this->assertStaff($actor);

        if (! in_array($hasil, [FormUsulan::HASIL_MS, FormUsulan::HASIL_TMS], true)) {
            throw new InvalidStatusTransition('Hasil verifikasi harus "ms" atau "tms".');
        }

        $to = $hasil === FormUsulan::HASIL_MS ? FormUsulan::STATUS_DISETUJUI : FormUsulan::STATUS_PERBAIKAN;
        $catatan = $this->clean($catatan);

        if ($hasil === FormUsulan::HASIL_TMS && $catatan === null && ! $this->hasFlaggedBerkas($usulan)) {
            throw InvalidStatusTransition::from($usulan->status_key, $to, 'Keputusan TMS wajib disertai catatan atau berkas yang ditandai perlu perbaikan.');
        }

        $changes = [
            'hasil_verifikasi' => $hasil,
            'verified_by' => $actor->id,
            'verified_at' => now(),
        ];
        if ($catatan !== null) {
            $changes['catatan'] = $catatan;
        }

        return $this->transition($usulan, $actor, FormUsulan::STATUS_VERIFIKASI, $to, $catatan, $changes, 'decide');
    }

    /**
     * 4. Perbaikan -> verifikasi lewat "Kirim ulang" oleh Pegawai pemilik.
     */
    public function resubmit(FormUsulan $usulan, User $actor): FormUsulan
    {
        $this->assertOwner($usulan, $actor);

        return $this->transition($usulan, $actor, FormUsulan::STATUS_PERBAIKAN, FormUsulan::STATUS_VERIFIKASI, null, [
            'submitted_at' => now(),
        ], 'resubmit');
    }

    /**
     * 5. Disetujui -> ditetapkan. Surat usulan (PDF) wajib.
     */
    public function issueSurat(FormUsulan $usulan, User $actor, ?string $suratPath, ?string $catatan = null): FormUsulan
    {
        $this->assertStaff($actor);

        if ($this->clean($suratPath) === null) {
            throw InvalidStatusTransition::from($usulan->status_key, FormUsulan::STATUS_DITETAPKAN, 'Surat usulan wajib diunggah.');
        }

        return $this->transition($usulan, $actor, FormUsulan::STATUS_DISETUJUI, FormUsulan::STATUS_DITETAPKAN, $this->clean($catatan), [
            'surat_usulan_path' => $suratPath,
        ], 'issueSurat');
    }

    /**
     * 6. Ditetapkan -> selesai. Sertifikat UKOM (PDF) wajib, hasil_ukom opsional [ASSUMPTION].
     */
    public function complete(FormUsulan $usulan, User $actor, ?string $sertifikatPath, ?string $hasilUkom = null, ?string $catatan = null): FormUsulan
    {
        $this->assertStaff($actor);

        if ($this->clean($sertifikatPath) === null) {
            throw InvalidStatusTransition::from($usulan->status_key, FormUsulan::STATUS_SELESAI, 'Sertifikat UKOM wajib diunggah.');
        }

        return $this->transition($usulan, $actor, FormUsulan::STATUS_DITETAPKAN, FormUsulan::STATUS_SELESAI, $this->clean($catatan), [
            'sertifikat_path' => $sertifikatPath,
            'hasil_ukom' => $this->clean($hasilUkom),
        ], 'complete');
    }

    /**
     * 7. Rollback (mundur) khusus Super Admin dengan alasan wajib.
     */
    public function rollback(FormUsulan $usulan, User $actor, string $to, ?string $reason): FormUsulan
    {
        if (! $actor->isSuperAdmin()) {
            throw new AuthorizationException('Hanya Super Admin yang dapat melakukan rollback status.');
        }

        $reason = $this->clean($reason);
        $from = $usulan->status_key;

        if ($reason === null) {
            throw InvalidStatusTransition::from($from, $to, 'Alasan rollback wajib diisi.');
        }

        if (! isset(self::RANK[$to], self::RANK[$from ?? '']) || self::RANK[$to] >= self::RANK[$from]) {
            throw InvalidStatusTransition::from($from, $to, 'Rollback hanya boleh ke status sebelumnya.');
        }

        return $this->transition($usulan, $actor, $from, $to, $reason, [], 'rollback');
    }

    /**
     * Terapkan perpindahan secara atomik setelah memastikan status saat ini sesuai.
     */
    private function transition(FormUsulan $usulan, User $actor, ?string $expectedFrom, string $to, ?string $catatan, array $changes, string $event): FormUsulan
    {
        $usulan = DB::transaction(function () use ($usulan, $actor, $expectedFrom, $to, $catatan, $changes) {
            $current = FormUsulan::whereKey($usulan->getKey())->lockForUpdate()->value('status_key');

            if ($current !== $expectedFrom) {
                throw InvalidStatusTransition::from($current, $to);
            }

            $usulan->forceFill($changes + ['status_key' => $to])->save();

            UsulanStatusLog::create([
                'form_usulan_id' => $usulan->id,
                'actor_id' => $actor->id,
                'from_status' => $current,
                'to_status' => $to,
                'catatan' => $catatan,
            ]);

            return $usulan;
        });

        // FR-NOT-1: notifikasi hanya dikirim setelah perubahan status tersimpan
        $this->notifications->usulanTransition($event, $usulan, $catatan);

        return $usulan;
    }

    private function assertStaff(User $actor): void
    {
        if (! $actor->isAdmin()) {
            throw new AuthorizationException('Hanya Tim Pembinaan JF atau Super Admin yang dapat mengubah status ini.');
        }
    }

    private function assertOwner(FormUsulan $usulan, User $actor): void
    {
        if (! $actor->isPegawai() || (int) $usulan->user_id !== (int) $actor->id) {
            throw new AuthorizationException('Hanya Pegawai pemilik usulan yang dapat melakukan tindakan ini.');
        }
    }

    private function hasFlaggedBerkas(FormUsulan $usulan): bool
    {
        return $usulan->berkas()->where('pemeriksaan', UsulanBerkas::PEMERIKSAAN_PERLU_PERBAIKAN)->exists();
    }

    private function clean(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        return $value === '' ? null : $value;
    }
}
