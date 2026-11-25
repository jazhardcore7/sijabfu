<?php

namespace App\Sso;

use App\Support\Satker;

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
        // FR-SATKER-4: data satuan kerja (opsional, ditambahkan tanpa mengubah field lama)
        public readonly ?string $satkerTipe = null,
        public readonly ?string $satkerKode = null,
        public readonly ?string $provinsiKode = null,
    ) {}

    /**
     * Field satker yang dapat ditentukan dari identitas ini (lihat App\Support\Satker).
     */
    public function satkerAttributes(): array
    {
        return Satker::resolve($this->satkerTipe, $this->satkerKode, $this->provinsiKode);
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
            satkerTipe: isset($data['satker_tipe']) ? (string) $data['satker_tipe'] : null,
            satkerKode: isset($data['satker_kode']) ? (string) $data['satker_kode'] : null,
            provinsiKode: isset($data['provinsi_kode']) ? (string) $data['provinsi_kode'] : null,
        );
    }
}
