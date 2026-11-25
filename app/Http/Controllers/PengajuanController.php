<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidStatusTransition;
use App\Models\FormUsulan;
use App\Models\User;
use App\Models\UsulanBerkas;
use App\Services\SimpegSyncService;
use App\Services\UsulanStateMachine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Modul Pengajuan Usulan oleh Pegawai (PRD Revisi 5.3).
 */
class PengajuanController extends Controller
{
    public function __construct(private UsulanStateMachine $stateMachine) {}

    public function index()
    {
        $user = Auth::user();
        $usulan = $this->activeUsulan($user);
        $latest = $this->latestUsulan($user);

        return view('modules.form-usulan', [
            'usulan' => $usulan,
            'usulanSelesai' => $usulan === null && $latest?->status_key === FormUsulan::STATUS_SELESAI ? $latest : null,
            'berkas' => $usulan ? $usulan->berkas()->get()->keyBy('jenis_persyaratan') : collect(),
            'persyaratan' => config('sijabfu.berkas_persyaratan'),
            'maxKb' => config('sijabfu.berkas_max_kb'),
            'jabfungList' => FormUsulan::listJabfung(),
            'jenisUsulanList' => FormUsulan::listJenisUsulan(),
        ]);
    }

    /**
     * Simpan entri data dan berkas (aksi=simpan), atau simpan lalu kirim (aksi=kirim).
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', FormUsulan::class);

        $user = Auth::user();
        $usulan = $this->activeUsulan($user);

        // FR-PU-5 / FR-PU-6: hanya draf atau perbaikan yang boleh diubah
        if ($usulan && ! $usulan->isEditableByPegawai()) {
            return redirect()->route('pengajuan')->with('error', 'Usulan Anda sedang diproses dan tidak dapat diubah.');
        }

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

        $aksi = $validated['aksi'] ?? 'simpan';
        $uploads = array_intersect_key($request->file('berkas', []), $persyaratan);
        $oldFiles = [];

        $usulan = DB::transaction(function () use ($usulan, $user, $validated, $uploads, &$oldFiles) {
            // FR-PU-2: identitas diambil dari user login, nilai kiriman diabaikan
            $data = [
                'nama_lengkap' => $user->name,
                'nip' => (string) $user->nip,
                'email' => $user->email,
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
                    'user_id' => $user->id,
                    'submitted_by' => $user->id, // FR-SATKER-8: diajukan sendiri
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

        if ($aksi === 'kirim') {
            return $this->kirim($usulan, $user);
        }

        return redirect()->route('pengajuan')->with('success', 'Data dan berkas usulan berhasil disimpan.');
    }

    /**
     * FR-PU-1: Sinkron data dengan SIMPEG/SIASN. Gagal -> peringatan, form tetap dapat dipakai.
     */
    public function sinkron(SimpegSyncService $simpeg): RedirectResponse
    {
        $result = $simpeg->syncUser(Auth::user());

        if (! $result['ok']) {
            return redirect()->route('pengajuan')->with('warning', $result['message']);
        }

        // Satuan Kerja di form diisi dari unit kerja SIMPEG/SIASN
        return redirect()->route('pengajuan')
            ->with('success', $result['message'])
            ->withInput(['satuan_kerja' => $result['data']['unit_kerja']]);
    }

    /**
     * Kirim ulang usulan dari status perbaikan (FR-PU-5).
     */
    public function kirimUlang(): RedirectResponse
    {
        $user = Auth::user();
        $usulan = $this->activeUsulan($user);

        if (! $usulan || $usulan->status_key !== FormUsulan::STATUS_PERBAIKAN) {
            return redirect()->route('pengajuan')->with('error', 'Kirim ulang hanya dapat dilakukan saat status Perbaikan.');
        }

        if ($missing = $usulan->missingBerkas()) {
            return $this->missingResponse($missing);
        }

        try {
            $this->stateMachine->resubmit($usulan, $user);
        } catch (InvalidStatusTransition $e) {
            return redirect()->route('pengajuan')->with('error', $e->getMessage());
        }

        return redirect()->route('pelacakan-status')->with('success', 'Usulan berhasil dikirim ulang dan menunggu verifikasi.');
    }

    /**
     * FR-PU-4: kirim hanya bila semua slot berkas sudah terisi.
     */
    private function kirim(FormUsulan $usulan, User $user): RedirectResponse
    {
        if (! $usulan->isDraft()) {
            return redirect()->route('pengajuan')->with('error', 'Gunakan tombol "Kirim ulang" untuk mengirim perbaikan.');
        }

        if ($missing = $usulan->missingBerkas()) {
            return $this->missingResponse($missing);
        }

        try {
            $this->stateMachine->submit($usulan, $user);
        } catch (InvalidStatusTransition $e) {
            return redirect()->route('pengajuan')->with('error', $e->getMessage());
        }

        return redirect()->route('pelacakan-status')->with('success', 'Usulan berhasil dikirim! Pantau statusnya di halaman ini.');
    }

    private function missingResponse(array $missing): RedirectResponse
    {
        $labels = array_map(fn ($key) => config('sijabfu.berkas_persyaratan')[$key], $missing);

        return redirect()->route('pengajuan')
            ->with('error', 'Usulan belum dapat dikirim. Berkas yang belum diunggah: '.implode(', ', $labels).'.');
    }

    private function latestUsulan(User $user): ?FormUsulan
    {
        return FormUsulan::where('user_id', $user->id)->orderByDesc('id')->first();
    }

    /**
     * FR-PU-6: usulan aktif = usulan terakhir yang belum selesai (termasuk draf).
     */
    private function activeUsulan(User $user): ?FormUsulan
    {
        $latest = $this->latestUsulan($user);

        return $latest && $latest->status_key !== FormUsulan::STATUS_SELESAI ? $latest : null;
    }
}
