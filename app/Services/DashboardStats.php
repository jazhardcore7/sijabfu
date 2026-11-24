<?php

namespace App\Services;

use App\Http\Controllers\PelacakanController;
use App\Models\FormUsulan;
use App\Models\SsoSyncLog;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Data grafik dashboard per role (FR-DASH-3, FR-DASH-4, FR-DASH-5).
 * Draf (status_key null) tidak dihitung.
 */
class DashboardStats
{
    /**
     * FR-DASH-4: usulan per status (urutan siklus hidup).
     */
    public function perStatus(): array
    {
        $counts = FormUsulan::submitted()
            ->selectRaw('status_key, count(*) as jumlah')
            ->groupBy('status_key')
            ->pluck('jumlah', 'status_key');

        $result = [];
        foreach (FormUsulan::STATUS_LABELS as $key => $label) {
            $result[$label] = (int) ($counts[$key] ?? 0);
        }

        return $result;
    }

    /**
     * FR-DASH-4: usulan per jenis_usulan (semua jenis yang dikenal, ditambah nilai lain bila ada).
     */
    public function perJenis(): array
    {
        $counts = FormUsulan::submitted()
            ->selectRaw('jenis_usulan, count(*) as jumlah')
            ->groupBy('jenis_usulan')
            ->pluck('jumlah', 'jenis_usulan');

        $result = [];
        foreach (FormUsulan::listJenisUsulan() as $jenis) {
            $result[$jenis] = (int) ($counts[$jenis] ?? 0);
        }
        foreach ($counts as $jenis => $jumlah) {
            $result[$jenis] ??= (int) $jumlah;
        }

        return $result;
    }

    /**
     * FR-DASH-4: usulan per bulan, 12 bulan terakhir, berdasarkan submitted_at
     * (baris lama tanpa submitted_at memakai created_at).
     */
    public function perBulan(?Carbon $now = null): array
    {
        $now = ($now ?? now())->copy()->startOfMonth();
        $start = $now->copy()->subMonths(11);

        $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $result = [];
        $keys = [];
        for ($i = 0; $i < 12; $i++) {
            $m = $start->copy()->addMonths($i);
            $label = $bulan[$m->month - 1].' '.$m->year;
            $keys[$m->format('Y-m')] = $label;
            $result[$label] = 0;
        }

        FormUsulan::submitted()
            ->get(['submitted_at', 'created_at'])
            ->each(function (FormUsulan $u) use (&$result, $keys) {
                $date = $u->submitted_at ?? $u->created_at;
                $key = $date?->format('Y-m');
                if ($key !== null && isset($keys[$key])) {
                    $result[$keys[$key]]++;
                }
            });

        return $result;
    }

    /**
     * FR-DASH-5: jumlah user per role.
     */
    public function usersPerRole(): array
    {
        $counts = User::selectRaw('role, count(*) as jumlah')->groupBy('role')->pluck('jumlah', 'role');

        $result = [];
        foreach (User::LEVEL_LABELS as $role => $label) {
            $result[$label] = (int) ($counts[$role] ?? 0);
        }

        return $result;
    }

    public function latestSsoSync(): ?SsoSyncLog
    {
        return SsoSyncLog::orderByDesc('id')->first();
    }

    /**
     * FR-DASH-3: progres usulan terakhir milik Pegawai pada lima status.
     */
    public function pegawai(User $user): array
    {
        $usulan = FormUsulan::where('user_id', $user->id)->submitted()->orderByDesc('id')->first();

        if (! $usulan) {
            return ['usulan' => null, 'tahapan' => [], 'persen' => 0];
        }

        $isSelesai = $usulan->status_key === FormUsulan::STATUS_SELESAI;
        $currentIndex = 0;
        foreach (PelacakanController::TAHAPAN as $i => $tahap) {
            if (in_array($usulan->status_key, $tahap['statuses'], true)) {
                $currentIndex = $i;
            }
        }

        $tahapan = [];
        foreach (PelacakanController::TAHAPAN as $i => $tahap) {
            $state = $isSelesai || $i < $currentIndex ? 'done'
                : ($i === $currentIndex ? ($usulan->status_key === FormUsulan::STATUS_PERBAIKAN ? 'perbaikan' : 'active') : 'pending');
            $tahapan[] = ['key' => $tahap['key'], 'label' => $tahap['label'], 'state' => $state];
        }

        return [
            'usulan' => $usulan,
            'tahapan' => $tahapan,
            'persen' => $isSelesai ? 100 : (int) round(($currentIndex / count(PelacakanController::TAHAPAN)) * 100),
        ];
    }
}
