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

        return view('auth.login', ['localEnabled' => config('sijabfu.auth_local_enabled')]);
    }

    /**
     * Proses autentikasi NIP & Password
     */
    public function login(Request $request)
    {
        // FR-AUTH-3: login lokal hanya bila AUTH_LOCAL_ENABLED=true
        if (!config('sijabfu.auth_local_enabled')) {
            return redirect()->route('login')->withErrors([
                'nip' => 'Login dengan NIP dan password dinonaktifkan. Silakan gunakan LOGIN SSO.',
            ]);
        }

        $credentials = $request->validate([
            'nip' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'nip.required' => 'Silahkan masukkan NIP Anda.',
            'password.required' => 'Silahkan masukkan Password Anda.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // User nonaktif tidak boleh masuk (FR-USR-1)
            if (!$user->is_active) {
                Auth::logout();

                return back()->withErrors([
                    'nip' => 'Akun Anda dinonaktifkan. Hubungi Super Admin.',
                ])->onlyInput('nip');
            }

            $request->session()->regenerate();

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
