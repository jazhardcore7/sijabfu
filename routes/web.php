<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessengerController;
use App\Http\Controllers\TimKerjaController;
use App\Http\Controllers\RegulasiController;
use App\Http\Controllers\Admin\RegulasiController as AdminRegulasiController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\VerifikasiController;
use App\Http\Controllers\PelacakanController;
use App\Http\Controllers\PengajuanController;

// 1. Auth Routes (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
});

// Logout Route (Authenticated)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// 2. Admin Routes (Tim Pembinaan JF dan Super Admin)
Route::middleware(['auth', 'role:tim_jf,super_admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/monitoring', [AdminController::class, 'monitoring'])->name('admin.monitoring');
    Route::post('/usulan/{id}/status', [AdminController::class, 'updateStatus'])->name('admin.usulan.update-status');
});

// 2b. Verifikasi Berkas, Monitoring Progress UKOM, dan Kelola Regulasi (Tim Pembinaan JF dan Super Admin)
Route::middleware(['auth', 'role:tim_jf,super_admin'])->group(function () {
    Route::get('/admin/regulasi', [AdminRegulasiController::class, 'index'])->name('admin.regulasi.index');
    Route::post('/admin/regulasi', [AdminRegulasiController::class, 'store'])->middleware('throttle:uploads')->name('admin.regulasi.store');
    Route::put('/admin/regulasi/{regulasi}', [AdminRegulasiController::class, 'update'])->whereNumber('regulasi')->middleware('throttle:uploads')->name('admin.regulasi.update');
    Route::delete('/admin/regulasi/{regulasi}', [AdminRegulasiController::class, 'destroy'])->whereNumber('regulasi')->name('admin.regulasi.destroy');

    // Messenger: inbox bersama (FR-CHAT-2, FR-CHAT-3); JSON untuk polling
    Route::get('/messenger', [MessengerController::class, 'index'])->name('messenger.index');
    Route::get('/messenger/{thread}', [MessengerController::class, 'show'])->whereNumber('thread')->name('messenger.show');
    Route::post('/messenger/{thread}', [MessengerController::class, 'reply'])->whereNumber('thread')->middleware('throttle:chat')->name('messenger.reply');

    Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
    Route::get('/verifikasi/{usulan}', [VerifikasiController::class, 'show'])->whereNumber('usulan')->name('verifikasi.show');
    Route::post('/verifikasi/{usulan}', [VerifikasiController::class, 'update'])->whereNumber('usulan')->name('verifikasi.update');

    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::post('/monitoring/{usulan}/status', [MonitoringController::class, 'updateStatus'])
        ->whereNumber('usulan')
        ->middleware('throttle:uploads')
        ->name('monitoring.update-status');
});

// 3. Menu khusus Pegawai
Route::middleware(['auth', 'role:pegawai'])->group(function () {
    // Pengajuan Usulan
    Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan');
    Route::post('/pengajuan', [PengajuanController::class, 'store'])->name('pengajuan.store')->middleware('throttle:uploads');
    Route::post('/pengajuan/kirim-ulang', [PengajuanController::class, 'kirimUlang'])->name('pengajuan.kirim-ulang');

    // Chat Box (FR-CHAT-1); JSON untuk polling
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat', [ChatController::class, 'store'])->middleware('throttle:chat')->name('chat.store');

    // Pelacakan Status
    Route::get('/pelacakan-status', [PelacakanController::class, 'index'])->name('pelacakan-status');

    // Route lama tetap berfungsi (dialihkan ke route baru)
    Route::redirect('/form-usulan', '/pengajuan')->name('form-usulan');
    Route::post('/form-usulan', [PengajuanController::class, 'store'])->name('form-usulan.store')->middleware('throttle:uploads');
    Route::redirect('/progress-berkas', '/pelacakan-status')->name('progress-berkas');
    Route::redirect('/uji-kompetensi', '/pelacakan-status');
});

// 4. Halaman untuk semua role yang sudah login
Route::middleware('auth')->group(function () {
    Route::get('/', [PortalController::class, 'index'])->name('home');

    // Dashboard per role (FR-DASH-3, FR-DASH-4, FR-DASH-5)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Notifikasi (FR-NOT-1)
    Route::get('/notifikasi/{id}', [NotifikasiController::class, 'open'])->name('notifikasi.open');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'readAll'])->name('notifikasi.baca-semua');
    Route::get('/alur-layanan', [PortalController::class, 'alurLayanan'])->name('alur-layanan');
    // Regulasi: daftar, preview, unduh (semua role) - FR-REG-1
    Route::get('/regulasi', [RegulasiController::class, 'index'])->name('regulasi');
    Route::get('/regulasi/{regulasi}/preview', [RegulasiController::class, 'preview'])->whereNumber('regulasi')->name('regulasi.preview');
    Route::get('/regulasi/{regulasi}/download', [RegulasiController::class, 'download'])->whereNumber('regulasi')->name('regulasi.download');
    // Tim Kerja (FR-TK-1)
    Route::get('/tim-kerja', [TimKerjaController::class, 'index'])->name('tim-kerja');
    Route::get('/tim-kerja/{user}', [TimKerjaController::class, 'show'])->whereNumber('user')->name('tim-kerja.show');

    // File usulan (pemilik, Tim Pembinaan JF, Super Admin) - FR-PS-6
    Route::get('/files/{type}/{id}', [FileController::class, 'show'])
        ->whereIn('type', ['berkas', 'surat', 'sertifikat'])
        ->whereNumber('id')
        ->name('files.show');
});
