<?php

namespace App\Sso;

use App\Contracts\SimpegDirectory;
use App\Contracts\SsoProvider;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dipakai bila SSO_DRIVER / SIMPEG_DRIVER bukan "fake": driver asli belum dibuat (PRD 11.1).
 */
class UnconfiguredSsoProvider implements SsoProvider, SimpegDirectory
{
    public function __construct(private string $driver)
    {
    }

    public function redirect(Request $request, string $state): Response
    {
        $this->fail();
    }

    public function identityFromCallback(Request $request): ?SsoIdentity
    {
        $this->fail();
    }

    public function directory(): iterable
    {
        $this->fail();
    }

    public function findByNip(string $nip): ?SsoIdentity
    {
        $this->fail();
    }

    private function fail(): never
    {
        throw new RuntimeException("Driver \"{$this->driver}\" belum dikonfigurasi.");
    }
}
