<?php

namespace App\Support;

use App\Models\User;

/**
 * Menentukan data satuan kerja dari direktori SSO/SIMPEG (keputusan 12, [ASSUMPTION]).
 *
 * satker_kode = kode wilayah BPS 4 digit: 0000 = Pusat, xx00 = Provinsi, lainnya = Kabupaten/Kota.
 * provinsi_kode = 2 digit pertama. satker_tipe eksplisit dari direktori diutamakan.
 * Mengembalikan hanya field yang dapat ditentukan; nilai kosong tidak pernah dikembalikan
 * sehingga tidak menghapus data yang sudah tersimpan (FR-SATKER-4, keputusan 14).
 *
 * @return array{satker_tipe?: string, satker_kode?: string, provinsi_kode?: string|null}
 */
final class Satker
{
    public static function resolve(?string $tipe, ?string $kode, ?string $provinsiKode): array
    {
        $tipe = self::clean($tipe);
        $kode = self::clean($kode);
        $provinsiKode = self::clean($provinsiKode);

        $result = [];

        if ($kode !== null && preg_match('/^\d{4}$/', $kode)) {
            $result['satker_kode'] = $kode;
            if ($tipe === null) {
                $tipe = $kode === '0000' ? User::SATKER_PUSAT : (str_ends_with($kode, '00') ? User::SATKER_PROVINSI : User::SATKER_KABKOTA);
            }
            if ($provinsiKode === null && $kode !== '0000') {
                $provinsiKode = substr($kode, 0, 2);
            }
        }

        if ($tipe !== null && array_key_exists($tipe, User::SATKER_LABELS)) {
            $result['satker_tipe'] = $tipe;
        }

        if (($result['satker_tipe'] ?? null) === User::SATKER_PUSAT) {
            $result['provinsi_kode'] = null;
        } elseif ($provinsiKode !== null && preg_match('/^\d{2}$/', $provinsiKode)) {
            $result['provinsi_kode'] = $provinsiKode;
        }

        return $result;
    }

    public static function provinsiLabel(?string $kode): string
    {
        if ($kode === null || $kode === '') {
            return '-';
        }

        return config('sijabfu.provinsi')[$kode] ?? $kode;
    }

    private static function clean(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        return $value === '' ? null : $value;
    }
}
