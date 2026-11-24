<?php

namespace Tests\Feature\Phase3;

use App\Models\FormUsulan;
use App\Models\User;
use App\Models\UsulanBerkas;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Helper data uji untuk Fase 3.
 */
trait UsulanFixtures
{
    protected function dataUsulan(array $override = []): array
    {
        return $override + [
            'aksi' => 'simpan',
            'jabatan_fungsional' => 'Statistisi',
            'jenis_usulan' => 'Inpassing',
            'nomor_surat' => '001/TEST/2026',
            'hal_surat' => 'Permohonan UKOM',
            'satuan_kerja' => 'BPS Provinsi Uji',
        ];
    }

    protected function pdf(string $name = 'berkas.pdf', int $kb = 100): UploadedFile
    {
        return UploadedFile::fake()->create($name, $kb, 'application/pdf');
    }

    /** @return array<string, UploadedFile> */
    protected function allBerkas(?array $only = null): array
    {
        $files = [];
        foreach (array_keys(config('sijabfu.berkas_persyaratan')) as $key) {
            if ($only === null || in_array($key, $only, true)) {
                $files[$key] = $this->pdf($key.'.pdf');
            }
        }

        return $files;
    }

    /**
     * Usulan langsung di database pada status tertentu, lengkap dengan 8 berkas di disk privat.
     */
    protected function usulanAt(User $owner, ?string $statusKey, array $extra = []): FormUsulan
    {
        $usulan = FormUsulan::create($extra + [
            'user_id' => $owner->id,
            'email' => $owner->email,
            'jabatan_fungsional' => 'Statistisi',
            'jenis_usulan' => 'Inpassing',
            'nomor_surat' => '001/TEST',
            'hal_surat' => 'Uji',
            'nama_lengkap' => $owner->name,
            'nip' => (string) $owner->nip,
            'satuan_kerja' => 'BPS RI',
            'link_berkas' => '',
            'status' => 1,
            'status_key' => $statusKey,
        ]);

        foreach (array_keys(config('sijabfu.berkas_persyaratan')) as $key) {
            $path = 'usulan/'.$usulan->id.'/'.$key.'.pdf';
            Storage::disk('local')->put($path, '%PDF-1.4 test');
            UsulanBerkas::create([
                'form_usulan_id' => $usulan->id,
                'jenis_persyaratan' => $key,
                'file_path' => $path,
                'original_name' => $key.'.pdf',
                'mime' => 'application/pdf',
                'size' => 14,
            ]);
        }

        return $usulan;
    }
}
