@extends('layouts.app')

@section('title', 'Kelola Regulasi')
@section('breadcrumb', 'Regulasi')

@section('content')
<div class="page-header">
    <h1 class="page-header-title"><x-icon name="book" /> Regulasi</h1>
    <p class="page-header-desc">Daftar peraturan, upload peraturan, hapus peraturan, edit peraturan, dan preview peraturan</p>
</div>

<div class="stack">
@if(session('success'))
<x-alert type="success">{{ session('success') }}</x-alert>
@endif

@if($errors->any())
<x-alert type="error" data-section="validation-errors">{{ implode(' ', $errors->all()) }}</x-alert>
@endif

{{-- UPLOAD PERATURAN --}}
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><x-icon name="upload" /> Upload Peraturan</h2>
        <span class="card-subtitle">PDF, maksimal {{ $maxMb }} MB</span>
    </div>
    <form action="{{ route('admin.regulasi.store') }}" method="POST" enctype="multipart/form-data" data-form="upload-regulasi">
        @csrf
        <div class="card-body reg-form-grid">
            <x-field name="judul" label="Judul / Tentang" required class="reg-full">
                <input type="text" id="judul" name="judul" value="{{ old('judul') }}" required>
            </x-field>
            <x-field name="nomor" label="Nomor Peraturan" required>
                <input type="text" id="nomor" name="nomor" value="{{ old('nomor') }}" placeholder="Contoh: Perka BPS No. 1 Tahun 2026" required>
            </x-field>
            <x-field name="tahun" label="Tahun" required>
                <input type="number" id="tahun" name="tahun" value="{{ old('tahun', now()->year) }}" min="1945" max="{{ now()->year + 1 }}" required>
            </x-field>
            <x-field name="kategori" label="Kategori" required>
                <select id="kategori" name="kategori" required>
                    @foreach($kategoriList as $kategori)
                    <option value="{{ $kategori }}" {{ old('kategori') === $kategori ? 'selected' : '' }}>{{ $kategori }}</option>
                    @endforeach
                </select>
            </x-field>
            <x-field name="file" label="File PDF" required>
                <input type="file" id="file" name="file" accept="application/pdf,.pdf" class="slot-file" required>
            </x-field>
        </div>
        <div class="card-footer form-actions">
            <button type="submit" class="btn btn--primary"><x-icon name="upload" /> Upload</button>
        </div>
    </form>
</div>

{{-- DAFTAR PERATURAN --}}
<div class="card card--flush">
    <div class="card-header">
        <h2 class="card-title"><x-icon name="book" /> Daftar Peraturan</h2>
        <span class="group-count" data-total="{{ $regulasi->total() }}">{{ $regulasi->total() }} peraturan</span>
    </div>

    <form action="{{ route('admin.regulasi.index') }}" method="GET" class="filter-bar" role="search">
        <div class="search-input-wrapper">
            <x-icon name="search" class="search-icon" />
            <label for="reg-q" class="sr-only">Cari judul atau nomor peraturan</label>
            <input type="search" id="reg-q" name="q" class="search-input" placeholder="Cari judul atau nomor peraturan..." value="{{ request('q') }}">
        </div>
        <button type="submit" class="btn btn--primary">Cari</button>
        @if(request('q'))
        <a href="{{ route('admin.regulasi.index') }}" class="btn btn--secondary">Reset</a>
        @endif
    </form>

    @if($regulasi->isEmpty())
    <x-empty-state icon="inbox" title="Belum ada peraturan." />
    @else
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Nomor &amp; Judul</th>
                    <th>Kategori</th>
                    <th>Tahun</th>
                    <th>File</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($regulasi as $reg)
                <tr data-regulasi="{{ $reg->id }}">
                    <td class="reg-tentang">
                        <div class="cell-strong">{{ $reg->nomor }}</div>
                        <div class="cell-sub">{{ $reg->judul }}</div>
                    </td>
                    <td>{{ $reg->kategori }}</td>
                    <td><span class="tahun-badge">{{ $reg->tahun }}</span></td>
                    <td>
                        @if($reg->hasFile())
                        <div class="btn-group-reg">
                            <a class="btn btn--secondary btn--sm" href="{{ route('regulasi.preview', $reg) }}" target="_blank" rel="noopener" data-action="preview-regulasi"><x-icon name="eye" /> Preview</a>
                            <a class="btn btn--secondary btn--sm" href="{{ route('regulasi.download', $reg) }}" data-action="download-regulasi"><x-icon name="download" /> Unduh</a>
                        </div>
                        @else
                        <span class="badge-muted">File belum tersedia</span>
                        @endif
                    </td>
                    <td>
                        <div class="cell-actions">
                            <button type="button" class="btn btn--secondary btn--sm" data-modal-open="edit-regulasi-{{ $reg->id }}"><x-icon name="pencil" /> Edit</button>
                            <button type="button" class="btn btn--danger btn--sm" data-modal-open="hapus-regulasi-{{ $reg->id }}"><x-icon name="trash" /> Hapus</button>
                        </div>

                        <x-modal id="edit-regulasi-{{ $reg->id }}" title="Edit" class="modal--wide">
                            <form action="{{ route('admin.regulasi.update', ['regulasi' => $reg->id] + request()->only('q', 'page')) }}" method="POST" enctype="multipart/form-data" class="reg-form-grid" data-form="edit-regulasi" id="form-edit-regulasi-{{ $reg->id }}">
                                @csrf
                                @method('PUT')
                                <div class="field reg-full">
                                    <label class="field-label" for="judul-{{ $reg->id }}">Judul / Tentang</label>
                                    <input type="text" id="judul-{{ $reg->id }}" name="judul" value="{{ $reg->judul }}" required>
                                </div>
                                <div class="field">
                                    <label class="field-label" for="nomor-{{ $reg->id }}">Nomor Peraturan</label>
                                    <input type="text" id="nomor-{{ $reg->id }}" name="nomor" value="{{ $reg->nomor }}" required>
                                </div>
                                <div class="field">
                                    <label class="field-label" for="tahun-{{ $reg->id }}">Tahun</label>
                                    <input type="number" id="tahun-{{ $reg->id }}" name="tahun" value="{{ $reg->tahun }}" min="1945" max="{{ now()->year + 1 }}" required>
                                </div>
                                <div class="field">
                                    <label class="field-label" for="kategori-{{ $reg->id }}">Kategori</label>
                                    <select id="kategori-{{ $reg->id }}" name="kategori" required>
                                        @foreach($kategoriList as $kategori)
                                        <option value="{{ $kategori }}" {{ $reg->kategori === $kategori ? 'selected' : '' }}>{{ $kategori }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="field">
                                    <label class="field-label" for="file-{{ $reg->id }}">Ganti File PDF (opsional)</label>
                                    <input type="file" id="file-{{ $reg->id }}" name="file" accept="application/pdf,.pdf" class="slot-file">
                                </div>
                            </form>
                            <x-slot:footer>
                                <button type="button" class="btn btn--secondary" data-modal-close>Batal</button>
                                <button type="submit" form="form-edit-regulasi-{{ $reg->id }}" class="btn btn--primary"><x-icon name="save" /> Simpan Perubahan</button>
                            </x-slot:footer>
                        </x-modal>

                        <form action="{{ route('admin.regulasi.destroy', ['regulasi' => $reg->id] + request()->only('q', 'page')) }}" method="POST" data-form="hapus-regulasi" id="form-hapus-regulasi-{{ $reg->id }}">
                            @csrf
                            @method('DELETE')
                        </form>
                        <x-modal id="hapus-regulasi-{{ $reg->id }}" title="Hapus peraturan {{ $reg->nomor }}?">
                            {{ $reg->judul }}
                            <x-slot:footer>
                                <button type="button" class="btn btn--secondary" data-modal-close>Batal</button>
                                <button type="submit" form="form-hapus-regulasi-{{ $reg->id }}" class="btn btn--danger"><x-icon name="trash" /> Hapus</button>
                            </x-slot:footer>
                        </x-modal>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($regulasi->hasPages())
    <div class="card-footer pagination-wrap">{{ $regulasi->links() }}</div>
    @endif
</div>
</div>
@endsection
