<?php

namespace App\Services;

use App\Contracts\SimpegDirectory;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sinkron data dengan SIMPEG/SIASN untuk Pengajuan Usulan (FR-PU-1).
 * Memperbarui nama, jabatan, dan unit kerja user; NIP tidak pernah diubah.
 */
class SimpegSyncService
{
    /**
     * @return array{ok: bool, message: string, data?: array}
     */
    public function syncUser(User $user): array
    {
        if (!$user->nip) {
            return ['ok' => false, 'message' => 'NIP akun Anda belum terisi, sinkron SIMPEG/SIASN tidak dapat dilakukan.'];
        }

        try {
            $identity = app(SimpegDirectory::class)->findByNip($user->nip);
        } catch (Throwable $e) {
            Log::warning('Sinkron SIMPEG gagal: ' . $e->getMessage());

            return ['ok' => false, 'message' => 'Sinkron SIMPEG/SIASN gagal. Data dari akun Anda tetap dapat dipakai.'];
        }

        if (!$identity) {
            return ['ok' => false, 'message' => 'Data NIP Anda tidak ditemukan di SIMPEG/SIASN. Data dari akun Anda tetap dapat dipakai.'];
        }

        $user->forceFill(array_filter([
            'name' => $identity->name,
            'jabatan' => $identity->jabatan,
            'unit_kerja' => $identity->unitKerja,
        ], fn ($v) => $v !== null && $v !== ''))->save();

        return [
            'ok' => true,
            'message' => 'Data berhasil disinkronkan dengan SIMPEG/SIASN.',
            'data' => [
                'nama' => $user->name,
                'nip' => $user->nip,
                'jabatan' => $user->jabatan,
                'unit_kerja' => $user->unit_kerja,
            ],
        ];
    }
}
