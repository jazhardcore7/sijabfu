<?php

namespace App\Http\Controllers;

use App\Models\FormUsulan;
use App\Models\User;
use App\Models\UsulanBerkas;
use Illuminate\Support\Facades\Auth;

/**
 * Modul Pelacakan Status Pegawai (PRD Revisi 5.4).
 */
class PelacakanController extends Controller
{
    /**
     * Tahapan tracker sesuai PDF. Status perbaikan ditampilkan di tahap Verifikasi.
     */
    public const TAHAPAN = [
        ['key' => 'verifikasi', 'label' => 'Verifikasi (Perbaikan)', 'statuses' => [FormUsulan::STATUS_VERIFIKASI, FormUsulan::STATUS_PERBAIKAN], 'desc' => 'Tim Pembinaan JF memeriksa kelengkapan berkas usulan.'],
        ['key' => 'disetujui', 'label' => 'Disetujui (MS/TMS)', 'statuses' => [FormUsulan::STATUS_DISETUJUI], 'desc' => 'Berkas dinyatakan Memenuhi Syarat (MS).'],
        ['key' => 'ditetapkan', 'label' => 'Ditetapkan', 'statuses' => [FormUsulan::STATUS_DITETAPKAN], 'desc' => 'Surat usulan diterbitkan dan dapat diunduh.'],
        ['key' => 'selesai', 'label' => 'Selesai', 'statuses' => [FormUsulan::STATUS_SELESAI], 'desc' => 'Sertifikat UKOM diunggah dan dapat diunduh.'],
    ];

    public function index()
    {
        $user = Auth::user();

        $usulan = FormUsulan::where('user_id', $user->id)->submitted()->orderByDesc('id')->first();
        $hasDraft = FormUsulan::where('user_id', $user->id)->whereNull('status_key')->exists();

        if (! $usulan) {
            return view('modules.progress-berkas', ['usulan' => null, 'hasDraft' => $hasDraft]);
        }

        $currentIndex = 0;
        foreach (self::TAHAPAN as $i => $tahap) {
            if (in_array($usulan->status_key, $tahap['statuses'], true)) {
                $currentIndex = $i;
            }
        }

        $isSelesai = $usulan->status_key === FormUsulan::STATUS_SELESAI;

        return view('modules.progress-berkas', [
            'usulan' => $usulan,
            'hasDraft' => $hasDraft,
            'tahapan' => self::TAHAPAN,
            'currentIndex' => $currentIndex,
            'isSelesai' => $isSelesai,
            'persen' => $isSelesai ? 100 : (int) round(($currentIndex / count(self::TAHAPAN)) * 100),
            'logs' => $usulan->statusLogs()->with('actor')->get(),
            'berkas' => $usulan->berkas()->get(),
            'flaggedBerkas' => $usulan->berkas()->where('pemeriksaan', UsulanBerkas::PEMERIKSAAN_PERLU_PERBAIKAN)->get(),
            'persyaratan' => config('sijabfu.berkas_persyaratan'),
            'showSurat' => in_array($usulan->status_key, [FormUsulan::STATUS_DITETAPKAN, FormUsulan::STATUS_SELESAI], true),
            'showSertifikat' => $isSelesai,
            'levelLabels' => User::LEVEL_LABELS,
        ]);
    }
}
