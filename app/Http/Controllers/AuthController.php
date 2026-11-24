<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Tampilkan form login (Attachment 2)
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Proses autentikasi NIP & Password
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'nip' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'nip.required' => 'Silahkan masukkan NIP Anda.',
            'password.required' => 'Silahkan masukkan Password Anda.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->isAdmin()) {
                return redirect()->intended(route('dashboard'))
                    ->with('success', 'Selamat datang di Dashboard Admin Pembinaan JF, ' . $user->name);
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang di Portal Pembinaan JF BPS RI, ' . $user->name);
        }

        return back()->withErrors([
            'nip' => 'NIP atau Password yang Anda masukkan tidak sesuai. Silahkan periksa kembali.',
        ])->onlyInput('nip');
    }

    /**
     * Logout pengguna
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah berhasil logout.');
    }
}
