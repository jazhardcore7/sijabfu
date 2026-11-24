<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\AdminController;

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

// 3. Menu khusus Pegawai
Route::middleware(['auth', 'role:pegawai'])->group(function () {
    Route::get('/form-usulan', [PortalController::class, 'formUsulan'])->name('form-usulan');
    Route::post('/form-usulan', [PortalController::class, 'formUsulanStore'])->name('form-usulan.store');
    Route::get('/progress-berkas', [PortalController::class, 'progressBerkas'])->name('progress-berkas');
    Route::redirect('/uji-kompetensi', '/progress-berkas');
});

// 4. Halaman untuk semua role yang sudah login
Route::middleware('auth')->group(function () {
    Route::get('/', [PortalController::class, 'index'])->name('home');
    Route::get('/alur-layanan', [PortalController::class, 'alurLayanan'])->name('alur-layanan');
    Route::get('/regulasi', [PortalController::class, 'regulasi'])->name('regulasi');
    Route::get('/tim-kerja', [PortalController::class, 'timKerja'])->name('tim-kerja');
});
