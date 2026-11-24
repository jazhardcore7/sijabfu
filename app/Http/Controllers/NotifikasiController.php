<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Notifikasi database (FR-NOT-1): buka (tandai dibaca lalu menuju halaman) dan tandai semua dibaca.
 */
class NotifikasiController extends Controller
{
    public function open(string $id): RedirectResponse
    {
        // hanya notifikasi milik user login; milik orang lain -> 404
        $notification = Auth::user()->notifications()->whereKey($id)->firstOrFail();
        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        // hanya alamat internal aplikasi
        if (! is_string($url) || ! str_starts_with($url, url('/'))) {
            return redirect()->route('dashboard');
        }

        return redirect()->to($url);
    }

    public function readAll(Request $request): RedirectResponse
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        return redirect()->back(fallback: route('dashboard'))->with('success', 'Semua notifikasi ditandai dibaca.');
    }
}
