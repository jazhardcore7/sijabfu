<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Regulasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Kelola Regulasi untuk Tim Pembinaan JF dan Super Admin (FR-REG-2):
 * daftar, upload, edit (ganti file), hapus (soft delete), preview.
 */
class RegulasiController extends Controller
{
    public function index(Request $request)
    {
        $regulasi = Regulasi::search($request->query('q'))
            ->orderBy('kategori')
            ->orderByDesc('tahun')
            ->orderBy('nomor')
            ->paginate(15)
            ->withQueryString();

        return view('admin.regulasi', [
            'regulasi' => $regulasi,
            'kategoriList' => Regulasi::KATEGORI,
            'maxMb' => config('sijabfu.regulasi_max_kb') / 1024,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Regulasi::class);

        $validated = $request->validate($this->rules(fileRequired: true), $this->messages());

        $path = Storage::disk(config('sijabfu.disk'))->putFile('regulasi', $request->file('file'));

        Regulasi::create([
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'],
            'nomor' => $validated['nomor'],
            'tahun' => $validated['tahun'],
            'file_path' => $path,
            'uploaded_by' => Auth::id(),
        ]);

        return redirect()->route('admin.regulasi.index')->with('success', 'Regulasi berhasil diunggah.');
    }

    public function update(Request $request, Regulasi $regulasi): RedirectResponse
    {
        Gate::authorize('update', $regulasi);

        $validated = $request->validate($this->rules(fileRequired: false), $this->messages());

        $data = [
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'],
            'nomor' => $validated['nomor'],
            'tahun' => $validated['tahun'],
        ];

        $oldPath = null;
        if ($request->hasFile('file')) {
            $oldPath = $regulasi->file_path;
            $data['file_path'] = Storage::disk(config('sijabfu.disk'))->putFile('regulasi', $request->file('file'));
            $data['uploaded_by'] = Auth::id();
        }

        $regulasi->update($data);

        if ($oldPath) {
            Storage::disk(config('sijabfu.disk'))->delete($oldPath);
        }

        return redirect()->route('admin.regulasi.index', $request->only('q', 'page'))->with('success', 'Regulasi berhasil diperbarui.');
    }

    /**
     * Soft delete; file tetap disimpan agar data dapat dipulihkan.
     */
    public function destroy(Request $request, Regulasi $regulasi): RedirectResponse
    {
        Gate::authorize('delete', $regulasi);

        $regulasi->delete();

        return redirect()->route('admin.regulasi.index', $request->only('q', 'page'))->with('success', 'Regulasi berhasil dihapus.');
    }

    private function rules(bool $fileRequired): array
    {
        return [
            'judul' => ['required', 'string', 'max:300'],
            'kategori' => ['required', Rule::in(Regulasi::KATEGORI)],
            'nomor' => ['required', 'string', 'max:200'],
            'tahun' => ['required', 'integer', 'min:1945', 'max:'.(now()->year + 1)],
            'file' => [$fileRequired ? 'required' : 'nullable', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:'.config('sijabfu.regulasi_max_kb')],
        ];
    }

    private function messages(): array
    {
        $maxMb = config('sijabfu.regulasi_max_kb') / 1024;

        return [
            'judul.required' => 'Judul wajib diisi.',
            'kategori.required' => 'Kategori wajib dipilih.',
            'kategori.in' => 'Kategori tidak dikenal.',
            'nomor.required' => 'Nomor peraturan wajib diisi.',
            'tahun.required' => 'Tahun wajib diisi.',
            'file.required' => 'File PDF regulasi wajib diunggah.',
            'file.mimes' => 'File regulasi harus berformat PDF.',
            'file.mimetypes' => 'File regulasi harus berformat PDF.',
            'file.max' => "Ukuran file regulasi maksimal {$maxMb} MB.",
        ];
    }
}
