<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\AdminController;

// 1. Auth Routes (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Logout Route (Authenticated)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// 2. Admin Routes (Khusus Role Admin / Tim Kerja / PIC)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
});

// 3. Portal Pegawai & Layanan (Membutuhkan Login)
Route::middleware('auth')->group(function () {
    Route::get('/', [PortalController::class, 'index'])->name('home');
    Route::get('/alur-layanan', [PortalController::class, 'alurLayanan'])->name('alur-layanan');
    Route::get('/form-usulan', [PortalController::class, 'formUsulan'])->name('form-usulan');
    Route::get('/uji-kompetensi', [PortalController::class, 'ujiKompetensi'])->name('uji-kompetensi');
    Route::get('/regulasi', [PortalController::class, 'regulasi'])->name('regulasi');
    Route::get('/tim-kerja', [PortalController::class, 'timKerja'])->name('tim-kerja');
});
