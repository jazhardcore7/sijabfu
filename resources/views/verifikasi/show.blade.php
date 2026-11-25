@extends('layouts.app')

@section('title', 'Verifikasi Berkas Usulan Pegawai')
@section('breadcrumb', 'Verifikasi Berkas Usulan Pegawai')

@section('content')
@php
    $bisaDiperiksa = $usulan->status_key === \App\Models\FormUsulan::STATUS_VERIFIKASI;
    $levelLabels = \App\Models\User::LEVEL_LABELS;
@endphp

<div class="page-header">
    <h1 class="page-header-title"><x-icon name="search-check" /> Pemeriksaan Berkas</h1>
    <p class="page-header-desc">
        <a href="{{ route('verifikasi.index') }}" class="link-back"><x-icon name="arrow-left" /> Daftar Pegawai</a>
    </p>
</div>

<div class="stack">
@if(session('success'))
<x-alert type="success">{{ session('success') }}</x-alert>
@endif

@if($transitionError)
<x-alert type="error" data-section="transition-error">{{ $transitionError }}</x-alert>
@endif

@if($errors->any())
<x-alert type="error" data-section="validation-errors">{{ implode(' ', $errors->all()) }}</x-alert>
@endif

{{-- DATA USULAN (FR-VB-2) --}}
<div class="card card--flush">
    <div class="card-header">
        <h2 class="card-title"><x-icon name="file-text" /> Data Usulan</h2>
        <span class="status-badge" data-status="{{ $usulan->status_key ?: 'draf' }}" data-status-key="{{ $usulan->status_key }}">{{ $usulan->statusKeyLabel() }}</span>
    </div>
    <dl class="pb-detail-grid">
        <div class="pb-detail-row"><dt class="pb-detail-label">Nama Lengkap</dt><dd class="pb-detail-value">{{ $usulan->nama_lengkap }}</dd></div>
        <div class="pb-detail-row"><dt class="pb-detail-label">NIP</dt><dd class="pb-detail-value">{{ $usulan->nip }}</dd></div>
        <div class="pb-detail-row"><dt class="pb-detail-label">Jabatan Fungsional</dt><dd class="pb-detail-value">{{ $usulan->jabatan_fungsional }}</dd></div>
        <div class="pb-detail-row"><dt class="pb-detail-label">Jenis Usulan</dt><dd class="pb-detail-value">{{ $usulan->jenis_usulan }}</dd></div>
        <div class="pb-detail-row"><dt class="pb-detail-label">Satuan Kerja</dt><dd class="pb-detail-value">{{ $usulan->satuan_kerja }}</dd></div>
        <div class="pb-detail-row" data-section="diajukan-oleh"><dt class="pb-detail-label">Diajukan oleh</dt><dd class="pb-detail-value">{{ $usulan->diajukanOlehLabel() }}</dd></div>
        <div class="pb-detail-row"><dt class="pb-detail-label">Email</dt><dd class="pb-detail-value">{{ $usulan->email }}</dd></div>
        <div class="pb-detail-row"><dt class="pb-detail-label">Nomor Surat</dt><dd class="pb-detail-value">{{ $usulan->nomor_surat }}</dd></div>
        <div class="pb-detail-row"><dt class="pb-detail-label">Hal Surat</dt><dd class="pb-detail-value">{{ $usulan->hal_surat }}</dd></div>
        <div class="pb-detail-row"><dt class="pb-detail-label">Hasil Verifikasi</dt><dd class="pb-detail-value">{{ $usulan->hasil_verifikasi ? strtoupper($usulan->hasil_verifikasi) : '-' }}</dd></div>
        <div class="pb-detail-row"><dt class="pb-detail-label">Catatan Terbaru</dt><dd class="pb-detail-value">{{ $usulan->catatan ?: '-' }}</dd></div>
    </dl>
    @include('modules.partials.legacy-links', ['usulan' => $usulan])
</div>

{{-- BERKAS + PEMERIKSAAN (FR-VB-2, FR-VB-3) --}}
<form action="{{ route('verifikasi.update', $usulan->id) }}" method="POST">
    @csrf
    <input type="hidden" name="aksi" value="periksa">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><x-icon name="file-text" /> Berkas Pegawai &amp; Pemeriksaan</h2>
            @unless($bisaDiperiksa)
            <span class="card-subtitle">Pemeriksaan hanya dapat diubah saat status Verifikasi</span>
            @endunless
        </div>
        <ul class="vb-berkas-list">
            @foreach($persyaratan as $key => $label)
                @php $file = $berkas->get($key); @endphp
                <li class="vb-berkas-row" data-berkas="{{ $key }}">
                    <div class="vb-berkas-main">
                        <div class="vb-berkas-title">{{ $loop->iteration }}. {{ $label }}</div>
                        @if($file)
                        <div class="cell-sub">{{ $file->original_name }}</div>
                        <div class="vb-links">
                            <a class="btn btn--secondary btn--sm" href="{{ route('files.show', ['type' => 'berkas', 'id' => $file->id, 'mode' => 'preview']) }}" target="_blank" rel="noopener" data-action="preview-berkas"><x-icon name="eye" /> Preview</a>
                            <a class="btn btn--secondary btn--sm" href="{{ route('files.show', ['type' => 'berkas', 'id' => $file->id]) }}" data-action="download-berkas"><x-icon name="download" /> Unduh</a>
                        </div>
                        @else
                        <span class="badge-muted">Belum diunggah</span>
                        @endif
                    </div>
                    @if($file)
                    <fieldset class="segmented" {{ $bisaDiperiksa ? '' : 'disabled' }}>
                        <legend class="sr-only">Hasil pemeriksaan {{ $label }}</legend>
                        <label><input type="radio" name="pemeriksaan[{{ $file->id }}][hasil]" value="sesuai" {{ $file->pemeriksaan === 'sesuai' ? 'checked' : '' }}><span><x-icon name="check" /> Sesuai</span></label>
                        <label><input type="radio" name="pemeriksaan[{{ $file->id }}][hasil]" value="perlu_perbaikan" {{ $file->pemeriksaan === 'perlu_perbaikan' ? 'checked' : '' }}><span><x-icon name="alert-triangle" /> Perlu perbaikan</span></label>
                    </fieldset>
                    <div class="vb-catatan">
                        <label for="catatan-berkas-{{ $file->id }}" class="sr-only">Catatan berkas {{ $label }}</label>
                        <input type="text" id="catatan-berkas-{{ $file->id }}" name="pemeriksaan[{{ $file->id }}][catatan]" class="vb-input" placeholder="Catatan berkas (opsional)" value="{{ $file->catatan }}" {{ $bisaDiperiksa ? '' : 'disabled' }}>
                    </div>
                    @endif
                </li>
            @endforeach
        </ul>
        @if($bisaDiperiksa && $berkas->isNotEmpty())
        <div class="card-footer form-actions">
            <button type="submit" class="btn btn--secondary" data-action="simpan-pemeriksaan"><x-icon name="save" /> Simpan Pemeriksaan</button>
        </div>
        @endif
    </div>
</form>

{{-- KEPUTUSAN MS/TMS (FR-VB-4, FR-VB-5) --}}
@if($bisaDiperiksa)
<form action="{{ route('verifikasi.update', $usulan->id) }}" method="POST" id="form-keputusan">
    @csrf
    <input type="hidden" name="aksi" value="keputusan">
    <div class="card">
        <div class="card-header"><h2 class="card-title"><x-icon name="shield" /> Keputusan MS/TMS</h2></div>
        <div class="card-body">
            <div class="field">
                <label class="field-label" for="catatanKeputusan">Catatan untuk pegawai (wajib untuk TMS, kecuali ada berkas yang ditandai "Perlu perbaikan")</label>
                <textarea id="catatanKeputusan" name="catatan" class="vb-textarea">{{ old('catatan') }}</textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn--primary" data-action="keputusan-ms" data-modal-open="modal-ms"><x-icon name="check-circle" /> MS — Memenuhi Syarat</button>
                <button type="button" class="btn btn--danger" data-action="keputusan-tms" data-modal-open="modal-tms"><x-icon name="alert-triangle" /> TMS — Perbaikan</button>
            </div>
        </div>
    </div>
</form>
<x-modal id="modal-ms" title="MS — Memenuhi Syarat">
    <strong>{{ $usulan->nama_lengkap }}</strong> — {{ $usulan->jenis_usulan }}
    <x-slot:footer>
        <button type="button" class="btn btn--secondary" data-modal-close>Batal</button>
        <button type="submit" form="form-keputusan" name="hasil" value="ms" class="btn btn--primary"><x-icon name="check-circle" /> MS — Memenuhi Syarat</button>
    </x-slot:footer>
</x-modal>
<x-modal id="modal-tms" title="TMS — Perbaikan">
    <strong>{{ $usulan->nama_lengkap }}</strong> — {{ $usulan->jenis_usulan }}
    <x-slot:footer>
        <button type="button" class="btn btn--secondary" data-modal-close>Batal</button>
        <button type="submit" form="form-keputusan" name="hasil" value="tms" class="btn btn--danger"><x-icon name="alert-triangle" /> TMS — Perbaikan</button>
    </x-slot:footer>
</x-modal>
@endif

{{-- RIWAYAT STATUS --}}
<div class="card">
    <div class="card-header"><h2 class="card-title"><x-icon name="clock" /> Riwayat Status</h2></div>
    @if($logs->isEmpty())
    <div class="card-body dash-muted">Belum ada riwayat.</div>
    @else
    <ul class="timeline">
        @foreach($logs as $log)
        <li>
            <div class="timeline-date">{{ $log->created_at->format('d M Y H:i') }}</div>
            <div>
                <div class="timeline-main">
                    {{ $log->from_status ? (\App\Models\FormUsulan::STATUS_LABELS[$log->from_status] ?? $log->from_status) . ' → ' : 'Dikirim → ' }}<strong>{{ \App\Models\FormUsulan::STATUS_LABELS[$log->to_status] ?? $log->to_status }}</strong>
                </div>
                <div class="timeline-actor">oleh {{ $levelLabels[$log->actor?->role] ?? 'Sistem' }}</div>
                @if($log->catatan)<div class="timeline-note">{{ $log->catatan }}</div>@endif
            </div>
        </li>
        @endforeach
    </ul>
    @endif
</div>
</div>
@endsection
