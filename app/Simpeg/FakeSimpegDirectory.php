<?php

namespace App\Simpeg;

use App\Contracts\SimpegDirectory;
use App\Sso\FakeDriverGuard;
use App\Sso\SsoIdentity;

/**
 * Driver SIMPEG/SIASN "fake": data fiktif dari config/sijabfu_fake.php.
 */
class FakeSimpegDirectory implements SimpegDirectory
{
    public function __construct()
    {
        FakeDriverGuard::assertAllowed();
    }

    public function findByNip(string $nip): ?SsoIdentity
    {
        $person = collect(config('sijabfu_fake.people', []))->firstWhere('nip', $nip);

        return $person ? SsoIdentity::fromArray($person) : null;
    }
}
