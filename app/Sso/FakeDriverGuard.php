<?php

namespace App\Sso;

use RuntimeException;

/**
 * Driver "fake" hanya untuk lokal/pengujian; ditolak saat APP_ENV=production.
 */
final class FakeDriverGuard
{
    public static function assertAllowed(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('Driver SSO/SIMPEG "fake" tidak diizinkan di production.');
        }
    }
}
