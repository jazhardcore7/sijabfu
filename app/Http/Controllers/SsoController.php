<?php

namespace App\Http\Controllers;

use App\Contracts\SsoProvider;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Login SSO lewat adapter (FR-AUTH-1). User dicocokkan berdasarkan NIP ke tabel users;
 * NIP tidak dikenal ditolak dan tidak ada akun yang dibuat.
 */
class SsoController extends Controller
{
    private const STATE_KEY = 'sso_state';

    public function redirect(Request $request): Response
    {
        try {
            $provider = app(SsoProvider::class);
            $state = Str::random(40);
            $request->session()->put(self::STATE_KEY, $state);

            return $provider->redirect($request, $state);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage());
        }
    }

    public function callback(Request $request): RedirectResponse
    {
        $expected = $request->session()->pull(self::STATE_KEY);
        $given = (string) $request->query('state', '');

        if (! $expected || ! hash_equals($expected, $given)) {
            return $this->fail('Sesi login SSO tidak valid atau kedaluwarsa. Silakan coba lagi.');
        }

        try {
            $identity = app(SsoProvider::class)->identityFromCallback($request);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage());
        }

        if (! $identity || $identity->nip === '') {
            return $this->fail('Login SSO gagal: identitas tidak diterima.');
        }

        $user = User::where('nip', $identity->nip)->first();

        if (! $user) {
            return $this->fail('NIP '.$identity->nip.' belum terdaftar di SIJABFU. Hubungi Super Admin.');
        }

        if (! $user->is_active) {
            return $this->fail('Akun Anda dinonaktifkan. Hubungi Super Admin.');
        }

        if (! $user->sso_id && $identity->ssoId && ! User::where('sso_id', $identity->ssoId)->exists()) {
            $user->forceFill(['sso_id' => $identity->ssoId])->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Selamat datang di SIGMA JF, '.$user->name);
    }

    private function fail(string $message): RedirectResponse
    {
        Log::info('SSO login ditolak: '.$message);

        return redirect()->route('login')->withErrors(['nip' => $message]);
    }
}
