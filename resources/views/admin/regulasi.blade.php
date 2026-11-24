@extends('layouts.app')

@section('title', 'Kelola Regulasi')
@section('breadcrumb', 'Regulasi')

@include('verifikasi._styles')

@push('styles')
<style>
    .reg-form-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 14px; }
    .reg-form-grid .full { grid-column: 1 / -1; }
    .reg-field { display: flex; flex-direction: column; gap: 5px; }
    .reg-field label { font-size: 12.5px; font-weight: 600; color: var(--c-text-2); }
    .reg-field .vb-input, .reg-field .vb-select { width: 100%; min-width: 0; }
    .reg-actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .reg-actions a, .reg-actions summary { font-size: 12px; font-weight: 700; color: var(--bps-mid); text-decoration: none; cursor: pointer; }
    .reg-edit { margin-top: 10px; padding: 14px; border: 1px dashed var(--c-border-strong); border-radius: 12px; background: var(--c-surface-2); }
    .btn-hapus { background: none; border: none; color: #DC2626; font-size: 12px; font-weight: 700; cursor: pointer; padding: 0; }
    .file-missing { font-size: 11.5px; color: #B45309; font-weight: 600; }
    @media (max-width: 768px) { .reg-form-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="page-header animate-fade-in vb-header">
    <h1 class="page-header-title">📋 Regulasi</h1>
    <p class="page-header-desc">Daftar peraturan, upload peraturan, hapus peraturan, edit peraturan, dan preview peraturan</p>
    <div class="page-header-divider"></div>
</div>

@if(session('success'))
<div class="vb-alert success"><span>✅</span><span>{{ session('success') }}</span></div>
@endif

@if($errors->any())
<div class="vb-alert error" data-section="validation-errors"><span>⚠️</span><span>{{ implode(' ', $errors->all()) }}</span></div>
@endif

{{-- UPLOAD PERATURAN --}}
<div class="vb-card">
    <div class="vb-card-header"><span>⬆️ Upload Peraturan</span><span class="vb-muted">PDF, maksimal {{ $maxMb }} MB</span></div>
    <div class="vb-card-body">
        <form action="{{ route('admin.regulasi.store') }}" method="POST" enctype="multipart/form-data" data-form="upload-regulasi">
            @csrf
            <div class="reg-form-grid">
                <div class="reg-field full">
                    <label for="judul">Judul / Tentang</label>
                    <input type="text" id="judul" name="judul" class="vb-input" value="{{ old('judul') }}" required>
                </div>
                <div class="reg-field">
                    <label for="nomor">Nomor Peraturan</label>
                    <input type="text" id="nomor" name="nomor" class="vb-input" value="{{ old('nomor') }}" placeholder="Contoh: Perka BPS No. 1 Tahun 2026" required>
                </div>
                <div class="reg-field">
                    <label for="tahun">Tahun</label>
                    <input type="number" id="tahun" name="tahun" class="vb-input" value="{{ old('tahun', now()->year) }}" min="1945" max="{{ now()->year + 1 }}" required>
                </div>
                <div class="reg-field">
                    <label for="kategori">Kategori</label>
                    <select id="kategori" name="kategori" class="vb-select" required>
                        @foreach($kategoriList as $kategori)
                        <option value="{{ $kategori }}" {{ old('kategori') === $kategori ? 'selected' : '' }}>{{ $kategori }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="reg-field">
                    <label for="file">File PDF</label>
                    <input type="file" id="file" name="file" accept="application/pdf,.pdf" required>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; margin-top:14px;">
                <button type="submit" class="vb-btn">⬆️ Upload</button>
            </div>
        </form>
    </div>
</div>

{{-- DAFTAR PERATURAN --}}
<div class="vb-card">
    <div class="vb-card-header">
        <span>📚 Daftar Peraturan</span>
        <span class="vb-muted" data-total="{{ $regulasi->total() }}">{{ $regulasi->total() }} peraturan</span>
    </div>

    <form action="{{ route('admin.regulasi.index') }}" method="GET" class="vb-filter">
        <input type="text" name="q" class="vb-input" placeholder="Cari judul atau nomor peraturan..." value="{{ request('q') }}">
        <button type="submit" class="vb-btn">Cari</button>
        @if(request('q'))
        <a href="{{ route('admin.regulasi.index') }}" class="vb-btn secondary">Reset</a>
        @endif
    </form>

    <table class="vb-table">
        <thead>
            <tr>
                <th>Nomor & Judul</th>
                <th>Kategori</th>
                <th>Tahun</th>
                <th>File</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($regulasi as $reg)
            <tr data-regulasi="{{ $reg->id }}">
                <td>
                    <div style="font-weight:700;">{{ $reg->nomor }}</div>
                    <div class="vb-muted">{{ $reg->judul }}</div>

                    <details class="reg-edit-toggle">
                        <summary class="vb-muted" style="margin-top:6px; cursor:pointer; font-weight:700; color:var(--bps-mid);">✏️ Edit</summary>
                        <form action="{{ route('admin.regulasi.update', ['regulasi' => $reg->id] + request()->only('q', 'page')) }}" method="POST" enctype="multipart/form-data" class="reg-edit" data-form="edit-regulasi">
                            @csrf
                            @method('PUT')
                            <div class="reg-form-grid">
                                <div class="reg-field full">
                                    <label>Judul / Tentang</label>
                                    <input type="text" name="judul" class="vb-input" value="{{ $reg->judul }}" required>
                                </div>
                                <div class="reg-field">
                                    <label>Nomor Peraturan</label>
                                    <input type="text" name="nomor" class="vb-input" value="{{ $reg->nomor }}" required>
                                </div>
                                <div class="reg-field">
                                    <label>Tahun</label>
                                    <input type="number" name="tahun" class="vb-input" value="{{ $reg->tahun }}" min="1945" max="{{ now()->year + 1 }}" required>
                                </div>
                                <div class="reg-field">
                                    <label>Kategori</label>
                                    <select name="kategori" class="vb-select" required>
                                        @foreach($kategoriList as $kategori)
                                        <option value="{{ $kategori }}" {{ $reg->kategori === $kategori ? 'selected' : '' }}>{{ $kategori }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="reg-field">
                                    <label>Ganti File PDF (opsional)</label>
                                    <input type="file" name="file" accept="application/pdf,.pdf">
                                </div>
                            </div>
                            <div style="display:flex; justify-content:flex-end; margin-top:10px;">
                                <button type="submit" class="vb-btn">💾 Simpan Perubahan</button>
                            </div>
                        </form>
                    </details>
                </td>
                <td>{{ $reg->kategori }}</td>
                <td>{{ $reg->tahun }}</td>
                <td>
                    @if($reg->hasFile())
                    <div class="reg-actions">
                        <a href="{{ route('regulasi.preview', $reg) }}" target="_blank" rel="noopener" data-action="preview-regulasi">👁 Preview</a>
                        <a href="{{ route('regulasi.download', $reg) }}" data-action="download-regulasi">⬇ Unduh</a>
                    </div>
                    @else
                    <span class="file-missing">File belum tersedia</span>
                    @endif
                </td>
                <td>
                    <form action="{{ route('admin.regulasi.destroy', ['regulasi' => $reg->id] + request()->only('q', 'page')) }}" method="POST"
                          onsubmit="return confirm('Hapus peraturan {{ addslashes($reg->nomor) }}?');" data-form="hapus-regulasi">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-hapus">🗑 Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; padding:40px; color:var(--c-text-muted);">Belum ada peraturan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="vb-pagination">{{ $regulasi->links() }}</div>
</div>
@endsection
