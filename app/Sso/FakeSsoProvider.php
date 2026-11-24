<?php

namespace App\Sso;

use App\Contracts\SsoProvider;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Driver SSO "fake" (FR-USR-3): halaman simulasi lokal tempat NIP diketik,
 * lalu kembali ke callback dengan state. Data dari config/sijabfu_fake.php (fiktif).
 * Tidak boleh dipakai di production (dicek di FakeDriverGuard).
 */
class FakeSsoProvider implements SsoProvider
{
    public function __construct()
    {
        FakeDriverGuard::assertAllowed();
    }

    public function redirect(Request $request, string $state): Response
    {
        return response()->view('auth.sso-fake', ['state' => $state]);
    }

    public function identityFromCallback(Request $request): ?SsoIdentity
    {
        $nip = trim((string) $request->query('nip', ''));
        if ($nip === '') {
            return null;
        }

        $person = collect($this->directory())->firstWhere('nip', $nip);

        // NIP di luar direktori fiktif tetap diteruskan; pencocokan ke tabel users dilakukan controller.
        return SsoIdentity::fromArray($person ?? ['nip' => $nip]);
    }

    public function directory(): iterable
    {
        return config('sijabfu_fake.people', []);
    }
}
