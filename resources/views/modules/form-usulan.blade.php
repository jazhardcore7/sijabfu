@extends('layouts.app')

@section('title', 'Pengajuan Usulan')
@section('breadcrumb', 'Pengajuan Usulan')

@section('content')
@php
    $editable = $usulan === null || $usulan->isEditableByPegawai();
    $isPerbaikan = $usulan?->status_key === \App\Models\FormUsulan::STATUS_PERBAIKAN;
    $authUser = auth()->user();
@endphp

<div class="page-header">
    <h1 class="page-header-title"><x-icon name="file-plus" /> Pengajuan Usulan</h1>
    <p class="page-header-desc">Entri data, upload berkas persyaratan, lalu kirim usulan jabatan fungsional Anda</p>
</div>

<div class="fu-wrapper">

    @if(session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
    @endif

    @if(session('warning'))
    <x-alert type="warning" data-section="sinkron-warning">{{ session('warning') }}</x-alert>
    @endif

    @if(session('error'))
    <x-alert type="error" data-section="error">{{ session('error') }}</x-alert>
    @endif

    @if($errors->any())
    <x-alert type="error" data-section="validation-errors">
        <ul class="alert-list">
            @foreach($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </x-alert>
    @endif

    @if($usulanSelesai)
    <x-alert type="info" title="Usulan sebelumnya telah Selesai" data-section="usulan-selesai">
        Anda dapat mengajukan usulan baru melalui formulir di bawah ini.
    </x-alert>
    @endif

    @if($isPerbaikan)
    <x-alert type="warning" title="Perbaikan: usulan Anda dinyatakan TMS" data-section="perbaikan-banner">
        {{ $usulan->catatan ?: 'Perbaiki berkas yang ditandai, lalu klik "Kirim ulang".' }}
    </x-alert>
    @endif

    @if(!$editable)
    {{-- ─── USULAN SEDANG DIPROSES (READ ONLY) ─── --}}
    <div class="pb-hero" data-section="usulan-diproses">
        <div class="pb-hero-top">
            <div>
                <div class="pb-hero-label">Usulan Sedang Diproses</div>
                <div class="pb-hero-name">{{ $usulan->nama_lengkap }}</div>
                <div class="pb-hero-meta">
                    <span class="pb-hero-chip"><x-icon name="user" /> {{ $usulan->jabatan_fungsional }}</span>
                    <span class="pb-hero-chip"><x-icon name="file-text" /> {{ $usulan->jenis_usulan }}</span>
                </div>
            </div>
            <x-status-badge :status="$usulan->status_key" :label="$usulan->statusKeyLabel()" />
        </div>
        <p class="pb-hero-note">
            Data dan berkas tidak dapat diubah selama usulan diproses.
            Pantau perkembangannya di menu <a href="{{ route('pelacakan-status') }}">Pelacakan Status</a>.
        </p>
    </div>

    <div class="card">
        <div class="card-header"><h2 class="card-title"><x-icon name="file-text" /> Berkas Persyaratan</h2></div>
        <div class="card-body">
            <ul class="slot-list">
                @foreach($persyaratan as $key => $label)
                    @php $file = $berkas->get($key); @endphp
                    <li class="slot-item">
                        <div class="slot-main">
                            <div class="slot-title">{{ $label }}</div>
                            @if($file)
                            <div class="slot-status ok"><x-icon name="check-circle" /> {{ $file->original_name }}</div>
                            @else
                            <div class="slot-status"><x-icon name="clock" /> Belum diunggah</div>
                            @endif
                        </div>
                        @if($file)
                        <div class="slot-actions">
                            <a class="btn btn--secondary btn--sm" href="{{ route('files.show', ['type' => 'berkas', 'id' => $file->id, 'mode' => 'preview']) }}" target="_blank" rel="noopener"><x-icon name="eye" /> Preview</a>
                            <a class="btn btn--secondary btn--sm" href="{{ route('files.show', ['type' => 'berkas', 'id' => $file->id]) }}"><x-icon name="download" /> Unduh</a>
                        </div>
                        @endif
                    </li>
                @endforeach
            </ul>
            @include('modules.partials.legacy-links', ['usulan' => $usulan])
        </div>
    </div>

    @else
    {{-- ─── SINKRON DATA SIMPEG/SIASN (FR-PU-1) ─── --}}
    <form action="{{ route('pengajuan.sinkron') }}" method="POST" class="card fu-sync" data-form="sinkron-simpeg">
        @csrf
        <div class="card-header">
            <div>
                <h2 class="card-title"><x-icon name="refresh" /> Sinkron Data dengan SIMPEG/SIASN</h2>
                <div class="card-subtitle">Perbarui nama, jabatan, dan unit kerja dari SIMPEG/SIASN</div>
            </div>
            <button type="submit" class="btn btn--secondary" data-action="sinkron-simpeg"><x-icon name="refresh" /> Sinkron Data</button>
        </div>
    </form>

    {{-- ─── FORM ENTRI DATA + UPLOAD BERKAS ─── --}}
    <form action="{{ route('pengajuan.store') }}" method="POST" enctype="multipart/form-data" id="formPengajuan">
        @csrf

        {{-- ENTRI DATA --}}
        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title"><x-icon name="user" /> Entri Data</h2>
                    <div class="card-subtitle">Identitas diambil dari akun Anda; lengkapi data usulan</div>
                </div>
            </div>
            <div class="card-body">
                <div class="fu-identity">
                    <div class="field">
                        <label class="field-label" for="fu-nama">Nama Lengkap</label>
                        <input id="fu-nama" type="text" value="{{ $authUser->name }}" readonly>
                    </div>
                    <div class="field">
                        <label class="field-label" for="fu-nip">NIP</label>
                        <input id="fu-nip" type="text" value="{{ $authUser->nip }}" readonly>
                    </div>
                    <div class="field">
                        <label class="field-label" for="fu-email">Email</label>
                        <input id="fu-email" type="text" value="{{ $authUser->email }}" readonly>
                    </div>
                    <div class="field">
                        <label class="field-label" for="fu-jabatan">Jabatan</label>
                        <input id="fu-jabatan" type="text" value="{{ $authUser->jabatan }}" readonly data-field="jabatan">
                    </div>
                </div>

                <div class="fu-grid">
                    <x-field name="nomor_surat" for="nomor_surat" label="Nomor Surat" required>
                        <input type="text" id="nomor_surat" name="nomor_surat" @error('nomor_surat') aria-invalid="true" aria-describedby="nomor_surat-error" @enderror
                            placeholder="Contoh: 001/BPS-RI/SDM/X/2026"
                            value="{{ old('nomor_surat', $usulan?->nomor_surat) }}" required>
                    </x-field>
                    <x-field name="hal_surat" for="hal_surat" label="Hal Surat" required>
                        <input type="text" id="hal_surat" name="hal_surat" @error('hal_surat') aria-invalid="true" aria-describedby="hal_surat-error" @enderror
                            placeholder="Contoh: Permohonan Uji Kompetensi Kenaikan Jenjang"
                            value="{{ old('hal_surat', $usulan?->hal_surat) }}" required>
                    </x-field>
                    <x-field name="satuan_kerja" for="satuan_kerja" label="Satuan Kerja" required class="fu-grid-full">
                        <input type="text" id="satuan_kerja" name="satuan_kerja" @error('satuan_kerja') aria-invalid="true" aria-describedby="satuan_kerja-error" @enderror
                            placeholder="Contoh: BPS Provinsi DKI Jakarta"
                            value="{{ old('satuan_kerja', $usulan?->satuan_kerja ?? $authUser->unit_kerja) }}" required>
                    </x-field>
                    @php $jfValue = old('jabatan_fungsional', $usulan?->jabatan_fungsional); @endphp
                    <x-field name="jabatan_fungsional" for="jabatan_fungsional" label="Jabatan Fungsional" required>
                        <select id="jabatan_fungsional" name="jabatan_fungsional" @error('jabatan_fungsional') aria-invalid="true" aria-describedby="jabatan_fungsional-error" @enderror required>
                            <option value="" disabled {{ $jfValue ? '' : 'selected' }}>-- Pilih Jabatan Fungsional --</option>
                            @foreach($jabfungList as $jf)
                            <option value="{{ $jf }}" {{ $jfValue === $jf ? 'selected' : '' }}>{{ $jf }}</option>
                            @endforeach
                        </select>
                    </x-field>
                    @php $juValue = old('jenis_usulan', $usulan?->jenis_usulan); @endphp
                    <x-field name="jenis_usulan" for="jenis_usulan" label="Jenis Usulan" required>
                        <select id="jenis_usulan" name="jenis_usulan" @error('jenis_usulan') aria-invalid="true" aria-describedby="jenis_usulan-error" @enderror required>
                            <option value="" disabled {{ $juValue ? '' : 'selected' }}>-- Pilih Jenis Usulan --</option>
                            @foreach($jenisUsulanList as $ju)
                            <option value="{{ $ju }}" {{ $juValue === $ju ? 'selected' : '' }}>{{ $ju }}</option>
                            @endforeach
                        </select>
                    </x-field>
                </div>
            </div>
        </div>

        {{-- UPLOAD BERKAS PERSYARATAN --}}
        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title"><x-icon name="upload" /> Upload Berkas Persyaratan</h2>
                    <div class="card-subtitle">Format PDF, maksimal {{ $maxKb / 1024 }} MB per berkas. Berkas dapat diunggah bertahap lalu disimpan.</div>
                </div>
            </div>
            <div class="card-body">
                <ul class="slot-list">
                    @foreach($persyaratan as $key => $label)
                        @php
                            $file = $berkas->get($key);
                            $flagged = $file && $file->pemeriksaan === \App\Models\UsulanBerkas::PEMERIKSAAN_PERLU_PERBAIKAN;
                        @endphp
                        <li class="slot-item {{ $flagged ? 'is-flagged' : '' }} {{ $errors->has('berkas.' . $key) ? 'has-error' : '' }}" data-slot="{{ $key }}">
                            <div class="slot-main">
                                <label class="slot-title" for="berkas-{{ $key }}">{{ $loop->iteration }}. {{ $label }} <span class="field-required" aria-hidden="true">*</span></label>
                                @if($file)
                                <div class="slot-status ok"><x-icon name="check-circle" /> {{ $file->original_name }}</div>
                                @else
                                <div class="slot-status"><x-icon name="clock" /> Belum diunggah</div>
                                @endif
                                @if($flagged)
                                <div class="slot-flag-note"><x-icon name="alert-triangle" /> Perlu perbaikan{{ $file->catatan ? ': ' . $file->catatan : '' }}</div>
                                @endif
                                @error('berkas.' . $key)<div class="field-error" id="berkas-{{ $key }}-error">{{ $message }}</div>@enderror
                            </div>
                            @if($file)
                            <div class="slot-actions">
                                <a class="btn btn--secondary btn--sm" href="{{ route('files.show', ['type' => 'berkas', 'id' => $file->id, 'mode' => 'preview']) }}" target="_blank" rel="noopener"><x-icon name="eye" /> Preview</a>
                                <a class="btn btn--secondary btn--sm" href="{{ route('files.show', ['type' => 'berkas', 'id' => $file->id]) }}"><x-icon name="download" /> Unduh</a>
                            </div>
                            @endif
                            <input type="file" id="berkas-{{ $key }}" name="berkas[{{ $key }}]" accept="application/pdf,.pdf" class="slot-file" @error('berkas.' . $key) aria-invalid="true" aria-describedby="berkas-{{ $key }}-error" @enderror>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="card-footer fu-submit-row">
                <button type="submit" name="aksi" value="simpan" class="btn btn--secondary"><x-icon name="save" /> Simpan</button>
                @unless($isPerbaikan)
                <button type="submit" name="aksi" value="kirim" class="btn btn--primary" data-action="kirim-usulan">
                    <x-icon name="send" /> Kirim Usulan
                </button>
                @endunless
            </div>
        </div>
    </form>

    @if($isPerbaikan)
    <form action="{{ route('pengajuan.kirim-ulang') }}" method="POST" class="fu-resend">
        @csrf
        <button type="submit" class="btn btn--primary" data-action="kirim-ulang">
            <x-icon name="send" /> Kirim Ulang
        </button>
    </form>
    @endif

    @if($usulan)
        @include('modules.partials.legacy-links', ['usulan' => $usulan])
    @endif
    @endif

</div>
@endsection

@push('scripts')
<script>
    // Tanda sedang mengirim pada tombol yang ditekan (mencegah kirim ganda).
    document.querySelectorAll('#formPengajuan, form[data-form="sinkron-simpeg"], .fu-resend').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var btn = e.submitter;
            if (btn) { setTimeout(function () { btn.setAttribute('aria-busy', 'true'); btn.setAttribute('aria-disabled', 'true'); }, 0); }
        });
    });
</script>
@endpush
