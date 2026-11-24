<?php

namespace App\Contracts;

use App\Sso\SsoIdentity;

/**
 * Adapter SIMPEG/SIASN (FR-PU-1). Implementasi: driver "fake"; driver asli menunggu PRD 11.1.
 */
interface SimpegDirectory
{
    /**
     * Data pegawai berdasarkan NIP; null bila tidak ditemukan.
     *
     * @throws \RuntimeException bila layanan tidak dapat dihubungi
     */
    public function findByNip(string $nip): ?SsoIdentity;
}
