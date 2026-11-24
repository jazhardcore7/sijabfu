<?php

namespace App\Sso;

/**
 * Identitas pegawai dari SSO atau SIMPEG/SIASN.
 */
final class SsoIdentity
{
    public function __construct(
        public readonly string $nip,
        public readonly ?string $ssoId = null,
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly ?string $jabatan = null,
        public readonly ?string $unitKerja = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            nip: trim((string) ($data['nip'] ?? '')),
            ssoId: $data['sso_id'] ?? null,
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            jabatan: $data['jabatan'] ?? null,
            unitKerja: $data['unit_kerja'] ?? null,
        );
    }
}
