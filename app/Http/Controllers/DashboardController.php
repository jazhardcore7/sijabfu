<?php

namespace App\Http\Controllers;

use App\Services\DashboardStats;
use Illuminate\Support\Facades\Auth;

/**
 * Dashboard per role (PRD Revisi 5.2 dan bagian 7: /dashboard untuk semua role).
 */
class DashboardController extends Controller
{
    public function __construct(private DashboardStats $stats) {}

    public function index()
    {
        $user = Auth::user();

        // FR-DASH-3: Pegawai
        if ($user->isPegawai()) {
            return view('landing', [
                'dashboard' => $this->stats->pegawai($user),
                'latestNotifications' => $user->notifications()->latest()->limit(5)->get(),
            ]);
        }

        // FR-DASH-4: Tim JF; FR-DASH-5: Super Admin (ditambah kartu SSO dan user per role)
        $extra = [
            'charts' => [
                'status' => $this->stats->perStatus(),
                'jenis' => $this->stats->perJenis(),
                'bulan' => $this->stats->perBulan(),
            ],
            'isSuperAdmin' => $user->isSuperAdmin(),
        ];

        if ($user->isSuperAdmin()) {
            $extra['ssoLog'] = $this->stats->latestSsoSync();
            $extra['usersPerRole'] = $this->stats->usersPerRole();
        }

        return app(AdminController::class)->dashboardView($extra);
    }
}
