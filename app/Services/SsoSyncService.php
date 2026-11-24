<?php

namespace App\Services;

use App\Contracts\SsoProvider;
use App\Models\SsoSyncLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

/**
 * Sinkronisasi Data SSO (FR-USR-2): upsert user berdasarkan NIP
 * (nama, email, jabatan, unit kerja). Role user yang sudah ada tidak pernah diubah.
 * User baru dibuat sebagai pegawai aktif dengan password acak yang tidak dipakai.
 * Setiap proses menulis satu baris sso_sync_logs.
 */
class SsoSyncService
{
    public function run(?User $triggeredBy): SsoSyncLog
    {
        $log = SsoSyncLog::create([
            'triggered_by' => $triggeredBy?->id,
            'started_at' => now(),
        ]);

        $created = $updated = $failed = 0;
        $errors = [];

        try {
            foreach (app(SsoProvider::class)->directory() as $row) {
                try {
                    $result = $this->upsert((array) $row);
                    $result === 'created' ? $created++ : ($result === 'updated' ? $updated++ : null);
                } catch (Throwable $e) {
                    $failed++;
                    $errors[] = $e->getMessage();
                }
            }
        } catch (Throwable $e) {
            $errors[] = 'Direktori SSO tidak dapat dibaca: '.$e->getMessage();
        }

        $log->update([
            'finished_at' => now(),
            'created_count' => $created,
            'updated_count' => $updated,
            'failed_count' => $failed,
            'error' => $errors ? Str::limit(implode(' | ', $errors), 2000) : null,
        ]);

        return $log;
    }

    /**
     * @return string created|updated|unchanged
     */
    private function upsert(array $row): string
    {
        $nip = trim((string) ($row['nip'] ?? ''));
        $data = [
            'name' => trim((string) ($row['name'] ?? '')),
            'email' => trim((string) ($row['email'] ?? '')),
            'jabatan' => $row['jabatan'] ?? null,
            'unit_kerja' => $row['unit_kerja'] ?? null,
        ];

        $validator = Validator::make(['nip' => $nip] + $data, [
            'nip' => ['required', 'string', 'max:25'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);
        if ($validator->fails()) {
            throw new \RuntimeException('Data NIP '.($nip ?: '(kosong)').' tidak valid: '.implode(', ', $validator->errors()->all()));
        }

        return DB::transaction(function () use ($nip, $data, $row) {
            $user = User::where('nip', $nip)->first();

            $emailOwner = User::where('email', $data['email'])->value('nip');
            if ($emailOwner !== null && $emailOwner !== $nip) {
                throw new \RuntimeException("Email {$data['email']} untuk NIP {$nip} sudah dipakai user lain.");
            }

            $ssoId = $row['sso_id'] ?? null;

            if (! $user) {
                $attrs = $data + [
                    'nip' => $nip,
                    'role' => User::ROLE_PEGAWAI,
                    'is_active' => true,
                    'password' => Hash::make(Str::random(48)),
                ];
                if ($ssoId && ! User::where('sso_id', $ssoId)->exists()) {
                    $attrs['sso_id'] = $ssoId;
                }
                User::forceCreate($attrs);

                return 'created';
            }

            // Role tidak pernah diubah oleh sinkronisasi
            $user->fill($data);
            if (! $user->sso_id && $ssoId && ! User::where('sso_id', $ssoId)->exists()) {
                $user->sso_id = $ssoId;
            }

            if (! $user->isDirty()) {
                return 'unchanged';
            }

            $user->save();

            return 'updated';
        });
    }
}
