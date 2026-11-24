<?php

namespace App\Contracts;

use App\Sso\SsoIdentity;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adapter SSO (FR-AUTH-1, FR-USR-2). Implementasi: driver "fake"; driver asli menunggu PRD 11.1.
 */
interface SsoProvider
{
    /**
     * Arahkan user ke halaman login penyedia SSO. $state disimpan di sesi oleh pemanggil.
     */
    public function redirect(Request $request, string $state): Response;

    /**
     * Ambil identitas dari callback; null bila callback tidak valid.
     */
    public function identityFromCallback(Request $request): ?SsoIdentity;

    /**
     * Daftar seluruh pegawai di direktori SSO (untuk Sinkronisasi Data SSO).
     *
     * @return iterable<array> data mentah per orang (nip, sso_id, name, email, jabatan, unit_kerja)
     */
    public function directory(): iterable;
}
