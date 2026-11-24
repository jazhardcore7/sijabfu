@extends('layouts.app')

@section('title', 'Verifikasi Berkas Usulan Pegawai')
@section('breadcrumb', 'Verifikasi Berkas Usulan Pegawai')

@include('verifikasi._styles')

@section('content')
@php
    $bisaDiperiksa = $usulan->status_key === \App\Models\FormUsulan::STATUS_VERIFIKASI;
    $levelLabels = \App\Models\User::LEVEL_LABELS;
@endphp

<div class="page-header animate-fade-in vb-header">
    <h1 class="page-header-title">🔍 Pemeriksaan Berkas</h1>
    <p class="page-header-desc">
        <a href="{{ route('verifikasi.index') }}" style="color:var(--bps-mid); font-weight:600; text-decoration:none;">← Daftar Pegawai</a>
    </p>
    <div class="page-header-divider"></div>
</div>

@if(session('success'))
<div class="vb-alert success"><span>✅</span><span>{{ session('success') }}</span></div>
@endif

@if($transitionError)
<div class="vb-alert error" data-section="transition-error"><span>⚠️</span><span>{{ $transitionError }}</span></div>
@endif

@if($errors->any())
<div class="vb-alert error" data-section="validation-errors"><span>⚠️</span><span>{{ implode(' ', $errors->all()) }}</span></div>
@endif

{{-- DATA USULAN (FR-VB-2) --}}
<div class="vb-card">
    <div class="vb-card-header">
        <span>📄 Data Usulan</span>
        <span class="vb-badge {{ $usulan->statusBadgeClass() }}" data-status-key="{{ $usulan->status_key }}">{{ $usulan->statusKeyLabel() }}</span>
    </div>
    <div class="vb-grid">
        <div><div class="vb-label">Nama Lengkap</div><div class="vb-value">{{ $usulan->nama_lengkap }}</div></div>
        <div><div class="vb-label">NIP</div><div class="vb-value">{{ $usulan->nip }}</div></div>
        <div><div class="vb-label">Jabatan Fungsional</div><div class="vb-value">{{ $usulan->jabatan_fungsional }}</div></div>
        <div><div class="vb-label">Jenis Usulan</div><div class="vb-value">{{ $usulan->jenis_usulan }}</div></div>
        <div><div class="vb-label">Satuan Kerja</div><div class="vb-value">{{ $usulan->satuan_kerja }}</div></div>
        <div><div class="vb-label">Email</div><div class="vb-value">{{ $usulan->email }}</div></div>
        <div><div class="vb-label">Nomor Surat</div><div class="vb-value">{{ $usulan->nomor_surat }}</div></div>
        <div><div class="vb-label">Hal Surat</div><div class="vb-value">{{ $usulan->hal_surat }}</div></div>
        <div><div class="vb-label">Hasil Verifikasi</div><div class="vb-value">{{ $usulan->hasil_verifikasi ? strtoupper($usulan->hasil_verifikasi) : '-' }}</div></div>
        <div><div class="vb-label">Catatan Terbaru</div><div class="vb-value">{{ $usulan->catatan ?: '-' }}</div></div>
    </div>
    @include('modules.partials.legacy-links', ['usulan' => $usulan])
</div>

{{-- BERKAS + PEMERIKSAAN (FR-VB-2, FR-VB-3) --}}
<form action="{{ route('verifikasi.update', $usulan->id) }}" method="POST">
    @csrf
    <input type="hidden" name="aksi" value="periksa">
    <div class="vb-card">
        <div class="vb-card-header">
            <span>📎 Berkas Pegawai & Pemeriksaan</span>
            @unless($bisaDiperiksa)
            <span class="vb-muted">Pemeriksaan hanya dapat diubah saat status Verifikasi</span>
            @endunless
        </div>
        <div class="vb-card-body">
            @foreach($persyaratan as $key => $label)
                @php $file = $berkas->get($key); @endphp
                <div class="vb-berkas-row" data-berkas="{{ $key }}">
                    <div>
                        <div class="vb-berkas-title">{{ $loop->iteration }}. {{ $label }}</div>
                        @if($file)
                        <div class="vb-muted">{{ $file->original_name }}</div>
                        <div class="vb-links">
                            <a href="{{ route('files.show', ['type' => 'berkas', 'id' => $file->id, 'mode' => 'preview']) }}" target="_blank" rel="noopener" data-action="preview-berkas">👁 Preview</a>
                            <a href="{{ route('files.show', ['type' => 'berkas', 'id' => $file->id]) }}" data-action="download-berkas">⬇ Unduh</a>
                        </div>
                        @else
                        <div class="vb-muted">Belum diunggah</div>
                        @endif
                    </div>
                    @if($file)
                    <div class="vb-radio">
                        <label><input type="radio" name="pemeriksaan[{{ $file->id }}][hasil]" value="sesuai" {{ $file->pemeriksaan === 'sesuai' ? 'checked' : '' }} {{ $bisaDiperiksa ? '' : 'disabled' }}> Sesuai</label>
                        <label><input type="radio" name="pemeriksaan[{{ $file->id }}][hasil]" value="perlu_perbaikan" {{ $file->pemeriksaan === 'perlu_perbaikan' ? 'checked' : '' }} {{ $bisaDiperiksa ? '' : 'disabled' }}> Perlu perbaikan</label>
                    </div>
                    <div>
                        <input type="text" name="pemeriksaan[{{ $file->id }}][catatan]" class="vb-input" style="width:100%; min-width:0;" placeholder="Catatan berkas (opsional)" value="{{ $file->catatan }}" {{ $bisaDiperiksa ? '' : 'disabled' }}>
                    </div>
                    @else
                    <div></div><div></div>
                    @endif
                </div>
            @endforeach

            @if($bisaDiperiksa && $berkas->isNotEmpty())
            <div style="display:flex; justify-content:flex-end; margin-top:14px;">
                <button type="submit" class="vb-btn secondary" data-action="simpan-pemeriksaan">💾 Simpan Pemeriksaan</button>
            </div>
            @endif
        </div>
    </div>
</form>

{{-- KEPUTUSAN MS/TMS (FR-VB-4, FR-VB-5) --}}
@if($bisaDiperiksa)
<form action="{{ route('verifikasi.update', $usulan->id) }}" method="POST">
    @csrf
    <input type="hidden" name="aksi" value="keputusan">
    <div class="vb-card">
        <div class="vb-card-header"><span>⚖️ Keputusan MS/TMS</span></div>
        <div class="vb-card-body">
            <label class="vb-label" for="catatanKeputusan">Catatan untuk pegawai (wajib untuk TMS, kecuali ada berkas yang ditandai "Perlu perbaikan")</label>
            <textarea id="catatanKeputusan" name="catatan" class="vb-textarea">{{ old('catatan') }}</textarea>
            <div class="vb-decision">
                <button type="submit" name="hasil" value="ms" class="vb-btn ms" data-action="keputusan-ms">✅ MS — Memenuhi Syarat</button>
                <button type="submit" name="hasil" value="tms" class="vb-btn tms" data-action="keputusan-tms">🛠️ TMS — Perbaikan</button>
            </div>
        </div>
    </div>
</form>
@endif

{{-- RIWAYAT STATUS --}}
<div class="vb-card">
    <div class="vb-card-header"><span>🕒 Riwayat Status</span></div>
    <div class="vb-card-body">
        @forelse($logs as $log)
        <div style="padding:8px 0; border-bottom:1px dashed var(--c-border); font-size:13px;">
            <span class="vb-muted">{{ $log->created_at->format('d M Y H:i') }}</span> —
            {{ $log->from_status ? (\App\Models\FormUsulan::STATUS_LABELS[$log->from_status] ?? $log->from_status) . ' → ' : 'Dikirim → ' }}<strong>{{ \App\Models\FormUsulan::STATUS_LABELS[$log->to_status] ?? $log->to_status }}</strong>
            <span class="vb-muted">oleh {{ $levelLabels[$log->actor?->role] ?? 'Sistem' }}</span>
            @if($log->catatan)<div class="vb-muted">{{ $log->catatan }}</div>@endif
        </div>
        @empty
        <div class="vb-muted">Belum ada riwayat.</div>
        @endforelse
    </div>
</div>
@endsection
