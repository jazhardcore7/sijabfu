<?php

namespace App\Services;

use App\Models\FormUsulan;
use App\Models\User;
use App\Models\UsulanBerkas;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Entri data dan upload berkas usulan (FR-PU-2, FR-PU-3), dipakai oleh Pengajuan Pegawai
 * dan Pengajuan oleh Admin Satuan Kerja Provinsi (FR-SATKER-8).
 */
class UsulanFormService
{
    /**
     * @return array{0: array, 1: array<string, UploadedFile>}
     */
    public function validate(Request $request): array
    {
        $persyaratan = config('sijabfu.berkas_persyaratan');
        $maxKb = config('sijabfu.berkas_max_kb');

        $validated = $request->validate([
            'aksi' => ['nullable', Rule::in(['simpan', 'kirim'])],
            'jabatan_fungsional' => ['required', 'string', Rule::in(FormUsulan::listJabfung())],
            'jenis_usulan' => ['required', 'string', Rule::in(FormUsulan::listJenisUsulan())],
            'nomor_surat' => ['required', 'string', 'max:200'],
            'hal_surat' => ['required', 'string', 'max:300'],
            'satuan_kerja' => ['required', 'string', 'max:200'],
            'berkas' => ['nullable', 'array'],
            'berkas.*' => ['nullable', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:'.$maxKb],
        ], [
            'jabatan_fungsional.required' => 'Jabatan Fungsional wajib dipilih.',
            'jenis_usulan.required' => 'Jenis Usulan wajib dipilih.',
            'nomor_surat.required' => 'Nomor Surat wajib diisi.',
            'hal_surat.required' => 'Hal Surat wajib diisi.',
            'satuan_kerja.required' => 'Satuan Kerja wajib diisi.',
            'berkas.*.mimes' => 'Berkas harus berformat PDF.',
            'berkas.*.mimetypes' => 'Berkas harus berformat PDF.',
            'berkas.*.max' => 'Ukuran berkas maksimal '.($maxKb / 1024).' MB.',
            'berkas.*.file' => 'Berkas gagal diunggah.',
        ]);

        return [$validated, array_intersect_key($request->file('berkas', []), $persyaratan)];
    }

    /**
     * Simpan data usulan dan berkas. Identitas (nama, NIP, email) selalu dari baris users
     * pegawai yang diusulkan; nilai kiriman diabaikan (FR-PU-2, FR-SATKER-8).
     */
    public function save(?FormUsulan $usulan, User $pegawai, User $submitter, array $validated, array $uploads): FormUsulan
    {
        $oldFiles = [];

        $usulan = DB::transaction(function () use ($usulan, $pegawai, $submitter, $validated, $uploads, &$oldFiles) {
            $data = [
                'nama_lengkap' => $pegawai->name,
                'nip' => (string) $pegawai->nip,
                'email' => $pegawai->email,
                'jabatan_fungsional' => $validated['jabatan_fungsional'],
                'jenis_usulan' => $validated['jenis_usulan'],
                'nomor_surat' => $validated['nomor_surat'],
                'hal_surat' => $validated['hal_surat'],
                'satuan_kerja' => $validated['satuan_kerja'],
            ];

            if ($usulan) {
                $usulan->update($data);
            } else {
                $usulan = FormUsulan::create($data + [
                    'user_id' => $pegawai->id,
                    'submitted_by' => $submitter->id,
                    'link_berkas' => '', // kolom lama NOT NULL; tidak dipakai untuk usulan baru
                    'status' => 1,
                ]);
            }

            foreach ($uploads as $jenis => $file) {
                $path = Storage::disk(config('sijabfu.disk'))->putFile('usulan/'.$usulan->id, $file);

                $existing = $usulan->berkas()->where('jenis_persyaratan', $jenis)->first();
                if ($existing) {
                    $oldFiles[] = $existing->file_path;
                }

                UsulanBerkas::updateOrCreate(
                    ['form_usulan_id' => $usulan->id, 'jenis_persyaratan' => $jenis],
                    [
                        'file_path' => $path,
                        'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
                        'mime' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        // berkas diganti: hasil pemeriksaan sebelumnya tidak berlaku lagi
                        'pemeriksaan' => null,
                        'catatan' => null,
                    ]
                );
            }

            return $usulan;
        });

        if ($oldFiles) {
            Storage::disk(config('sijabfu.disk'))->delete($oldFiles);
        }

        return $usulan;
    }

    /**
     * Pesan bila masih ada slot berkas kosong (FR-PU-4); null bila lengkap.
     */
    public function missingMessage(FormUsulan $usulan): ?string
    {
        $missing = $usulan->missingBerkas();
        if (! $missing) {
            return null;
        }

        $labels = array_map(fn ($key) => config('sijabfu.berkas_persyaratan')[$key], $missing);

        return 'Usulan belum dapat dikirim. Berkas yang belum diunggah: '.implode(', ', $labels).'.';
    }

    /**
     * Data tampilan bersama untuk partial form pengajuan.
     */
    public function viewData(?FormUsulan $usulan): array
    {
        return [
            'berkas' => $usulan ? $usulan->berkas()->get()->keyBy('jenis_persyaratan') : collect(),
            'persyaratan' => config('sijabfu.berkas_persyaratan'),
            'maxKb' => config('sijabfu.berkas_max_kb'),
            'jabfungList' => FormUsulan::listJabfung(),
            'jenisUsulanList' => FormUsulan::listJenisUsulan(),
        ];
    }
}
