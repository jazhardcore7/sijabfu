<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SsoSyncLog;
use App\Services\SsoSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Sinkronisasi Data SSO (FR-USR-2), khusus Super Admin.
 */
class SsoSyncController extends Controller
{
    public function index()
    {
        return view('admin.sso-sync', [
            'logs' => SsoSyncLog::with('triggeredBy')->orderByDesc('id')->paginate(15),
            'driver' => config('sijabfu.sso.driver'),
        ]);
    }

    public function store(SsoSyncService $sync): RedirectResponse
    {
        $log = $sync->run(Auth::user());

        // Direktori tidak terbaca sama sekali: tidak ada baris yang diproses
        $nothingProcessed = $log->created_count + $log->updated_count + $log->failed_count === 0;
        if ($log->error && $nothingProcessed) {
            return redirect()->route('admin.sso-sync')->with('error', 'Sinkronisasi gagal: '.$log->error);
        }

        return redirect()->route('admin.sso-sync')->with('success',
            "Sinkronisasi selesai: {$log->created_count} user baru, {$log->updated_count} diperbarui, {$log->failed_count} gagal.");
    }
}
