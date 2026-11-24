@extends('layouts.app')

@section('title', 'Pengajuan Usulan')
@section('breadcrumb', 'Pengajuan Usulan')

@push('styles')
<style>
    /* ─── CONTAINER ─── */
    .fu-wrapper {
        max-width: 900px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    /* ─── HERO CARD (PROGRESS) ─── */
    .pb-hero {
        background: linear-gradient(135deg, #1e3a5f 0%, #003b73 50%, #005baa 100%);
        border-radius: 24px;
        padding: 32px 36px;
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0,59,115,0.2);
    }
    .pb-hero::before {
        content: '';
        position: absolute; top: -60px; right: -60px;
        width: 240px; height: 240px;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
    }
    .pb-hero-top {
        display: flex; align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap; gap: 16px;
        margin-bottom: 24px;
    }
    .pb-hero-label {
        font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        color: rgba(255,255,255,0.6); margin-bottom: 4px;
    }
    .pb-hero-name { font-size: 22px; font-weight: 900; }
    .pb-hero-meta { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px; }
    .pb-hero-chip {
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 20px;
        padding: 4px 12px;
        font-size: 12px; font-weight: 600;
    }
    .pb-badge-aktif {
        background: rgba(52,211,153,0.2);
        border: 1.5px solid rgba(52,211,153,0.5);
        color: #6ee7b7;
        border-radius: 20px;
        padding: 6px 16px;
        font-size: 12px; font-weight: 700;
        white-space: nowrap;
    }
    .pb-progress-label {
        display: flex; justify-content: space-between;
        font-size: 13px; font-weight: 600;
        color: rgba(255,255,255,0.8);
        margin-bottom: 8px;
    }
    .pb-progress-track {
        background: rgba(255,255,255,0.15);
        border-radius: 50px; height: 10px; overflow: hidden;
    }
    .pb-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #34d399, #10b981);
        border-radius: 50px;
        transition: width 1s ease-in-out;
    }

    /* ─── ALUR SWIMLANE (ATTACHMENT 1) ─── */
    .pb-swimlane {
        background: var(--c-surface);
        border-radius: 20px;
        border: 1px solid var(--c-border);
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
    }
    .pb-swimlane-title {
        background: var(--c-surface-2);
        border-bottom: 1px solid var(--c-border);
        padding: 16px 24px;
        font-size: 14px; font-weight: 700; color: var(--c-text);
        display: flex; align-items: center; justify-content: space-between;
    }
    .pb-lanes { padding: 20px 24px; display: flex; flex-direction: column; gap: 0; }
    .pb-lane {
        display: grid;
        grid-template-columns: 210px 1fr;
        min-height: 85px;
        position: relative;
    }
    .pb-lane:not(:last-child) {
        border-bottom: 1px dashed var(--c-border);
    }
    .pb-lane-actor {
        display: flex; align-items: center;
        padding: 14px 18px 14px 0;
        gap: 12px;
        border-right: 2px solid var(--c-border-soft);
        margin-right: 18px;
    }
    .pb-lane-actor-badge {
        width: 38px; height: 38px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }
    .pb-lane-actor-name {
        font-size: 12px; font-weight: 700; color: var(--c-text-2); line-height: 1.4;
    }
    .pb-lane-step {
        display: flex; align-items: center;
        padding: 14px 0;
        gap: 14px;
    }
    .pb-step-num {
        width: 34px; height: 34px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 800; flex-shrink: 0;
    }
    .pb-step-num.done  { background: #d1fae5; color: #065f46; }
    .pb-step-num.active { background: #dbeafe; color: #1e40af; box-shadow: 0 0 0 4px rgba(59,130,246,0.18); font-weight: 900; }
    .pb-step-num.pending { background: var(--c-surface-3); color: var(--c-text-faint); }

    .pb-step-content { flex: 1; }
    .pb-step-label { font-size: 13.5px; font-weight: 700; color: var(--c-text); margin-bottom: 2px; }
    .pb-step-desc  { font-size: 12px; color: var(--c-text-muted); line-height: 1.45; }
    .pb-step-badge {
        font-size: 11px; font-weight: 700;
        padding: 4px 10px; border-radius: 20px;
        white-space: nowrap; flex-shrink: 0;
    }
    .pb-step-badge.done    { background: #d1fae5; color: #065f46; }
    .pb-step-badge.active  { background: #dbeafe; color: #1e40af; }
    .pb-step-badge.pending { background: var(--c-surface-3); color: var(--c-text-faint); }

    /* ─── CATATAN ADMIN ─── */
    .catatan-card {
        background: #fffbeb;
        border: 1.5px solid #fcd34d;
        border-radius: 16px;
        padding: 16px 20px;
        display: flex; gap: 14px; align-items: flex-start;
    }
    .catatan-icon { font-size: 22px; flex-shrink: 0; }
    .catatan-title { font-weight: 700; color: #92400e; margin-bottom: 3px; font-size: 13px; }
    .catatan-text  { font-size: 13px; color: #78350f; line-height: 1.55; }

    /* ─── DETAIL USULAN ─── */
    .pb-detail-card {
        background: var(--c-surface);
        border-radius: 20px;
        border: 1px solid var(--c-border);
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
    }
    .pb-detail-header {
        background: var(--c-surface-2);
        border-bottom: 1px solid var(--c-border);
        padding: 14px 24px;
        font-size: 14px; font-weight: 700; color: var(--c-text);
        display: flex; align-items: center; justify-content: space-between;
    }
    .pb-detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
    }
    .pb-detail-row {
        padding: 12px 24px;
        border-bottom: 1px solid var(--c-border-soft);
    }
    .pb-detail-row:nth-child(odd) { border-right: 1px solid var(--c-border-soft); }
    .pb-detail-label { font-size: 11px; color: var(--c-text-faint); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
    .pb-detail-value { font-size: 13.5px; font-weight: 600; color: var(--c-text); }
    .pb-detail-link  { font-size: 13px; color: #005baa; word-break: break-all; }

    /* ─── INFO BANNER UNTUK FORM BARU ─── */
    .fu-info-banner {
        background: linear-gradient(135deg, var(--bps-dark), #00509D);
        border-radius: 20px;
        padding: 24px 28px;
        color: white;
        display: flex;
        align-items: center;
        gap: 20px;
    }
    .fu-info-icon {
        width: 56px; height: 56px;
        background: rgba(255,255,255,0.15);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 26px; flex-shrink: 0;
    }
    .fu-info-title { font-size: 17px; font-weight: 800; margin-bottom: 5px; }
    .fu-info-desc  { font-size: 13px; color: rgba(255,255,255,0.8); line-height: 1.5; }

    /* ─── FORM CARD ─── */
    .fu-card {
        background: var(--c-surface);
        border-radius: 20px;
        border: 1px solid var(--c-border);
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
    }
    .fu-card-header {
        background: var(--c-surface-2);
        border-bottom: 1px solid var(--c-border);
        padding: 16px 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .fu-card-header-icon {
        width: 34px; height: 34px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
    }
    .fu-card-header-title { font-size: 14.5px; font-weight: 700; color: var(--c-heading); }
    .fu-card-header-subtitle { font-size: 12px; color: var(--c-text-muted); }
    .fu-card-body { padding: 24px; }

    .fu-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .fu-grid-full { grid-column: 1 / -1; }

    .fu-field { display: flex; flex-direction: column; gap: 5px; }
    .fu-label {
        font-size: 13px; font-weight: 600; color: var(--c-text-2);
        display: flex; align-items: center; gap: 4px;
    }
    .fu-label .req { color: #ef4444; }
    .fu-input, .fu-select {
        border: 1.5px solid var(--c-border-strong);
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 14px;
        color: var(--c-text);
        background: var(--c-surface);
        transition: border-color 0.2s, box-shadow 0.2s;
        font-family: inherit;
        width: 100%;
        box-sizing: border-box;
    }
    .fu-input:focus, .fu-select:focus {
        outline: none;
        border-color: var(--bps-mid);
        box-shadow: 0 0 0 3px rgba(0,91,170,0.1);
    }
    .fu-input.is-invalid, .fu-select.is-invalid { border-color: #ef4444; }
    .fu-select { cursor: pointer; }
    .fu-hint { font-size: 11.5px; color: var(--c-text-muted); line-height: 1.45; margin-top: 2px; }
    .fu-error { font-size: 12px; color: #ef4444; margin-top: 2px; }

    .fu-link-info {
        background: #fff7ed;
        border: 1.5px solid #fed7aa;
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 12px;
        font-size: 13px;
        color: #92400e;
        line-height: 1.55;
    }
    .fu-link-info strong { color: #7c2d12; }

    .fu-submit-row {
        display: flex;
        align-items: center;
        gap: 16px;
        justify-content: flex-end;
        padding-top: 16px;
        border-top: 1px solid var(--c-border-soft);
        margin-top: 8px;
    }
    .fu-btn-submit {
        background: linear-gradient(135deg, var(--bps-dark), #00509D);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .fu-btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,59,115,0.3); }

    .alert-success-bar {
        background: #d1fae5; border: 1.5px solid #6ee7b7; border-radius: 12px;
        padding: 14px 18px; color: #065f46; font-size: 13.5px; font-weight: 600;
        display: flex; align-items: center; gap: 10px;
    }

    @media (max-width: 640px) {
        .fu-grid { grid-template-columns: 1fr; }
        .pb-lane { grid-template-columns: 1fr; }
        .pb-lane-actor { border-right: none; border-bottom: 1px solid var(--c-border-soft); margin-right: 0; padding-bottom: 8px; }
        .pb-detail-grid { grid-template-columns: 1fr; }
        .pb-detail-row:nth-child(odd) { border-right: none; }
    }

    /* ─── SLOT BERKAS PERSYARATAN ─── */
    .slot-list { display: flex; flex-direction: column; gap: 12px; }
    .slot-item {
        border: 1.5px solid var(--c-border);
        border-radius: 14px;
        padding: 14px 16px;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 8px 16px;
        align-items: center;
        background: var(--c-surface);
    }
    .slot-item.is-flagged { border-color: #f59e0b; background: #fffbeb; }
    .slot-item.is-flagged .slot-title { color: #92400e; }
    .slot-title { font-size: 13.5px; font-weight: 700; color: var(--c-text); }
    .slot-status { font-size: 12px; color: var(--c-text-muted); margin-top: 2px; word-break: break-all; }
    .slot-status.ok { color: #16a34a; font-weight: 600; }
    .slot-flag-note { font-size: 12px; color: #92400e; margin-top: 4px; }
    .slot-actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .slot-actions a { font-size: 12px; font-weight: 700; color: var(--bps-mid); text-decoration: none; }
    .slot-file { grid-column: 1 / -1; font-size: 12.5px; color: var(--c-text-2); }
    .fu-btn-secondary {
        background: var(--c-surface-2);
        color: var(--c-text);
        border: 1.5px solid var(--c-border-strong);
        padding: 12px 24px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
    }
    .alert-error-bar {
        background: #fee2e2; border: 1.5px solid #fca5a5; border-radius: 12px;
        padding: 14px 18px; color: #991b1b; font-size: 13.5px; font-weight: 600;
        display: flex; align-items: center; gap: 10px;
    }
    .readonly-identity { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 18px; }
    .readonly-identity .fu-input { background: var(--c-surface-2); color: var(--c-text-2); }
    @media (max-width: 640px) {
        .readonly-identity { grid-template-columns: 1fr; }
        .slot-item { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
@php
    $editable = $usulan === null || $usulan->isEditableByPegawai();
    $isPerbaikan = $usulan?->status_key === \App\Models\FormUsulan::STATUS_PERBAIKAN;
    $authUser = auth()->user();
@endphp

<div class="page-header animate-fade-in">
    <h1 class="page-header-title">📝 Pengajuan Usulan</h1>
    <p class="page-header-desc">Entri data, upload berkas persyaratan, lalu kirim usulan jabatan fungsional Anda</p>
    <div class="page-header-divider"></div>
</div>

<div class="fu-wrapper">

    @if(session('success'))
    <div class="alert-success-bar"><span>✅</span> {{ session('success') }}</div>
    @endif

    @if(session('warning'))
    <div class="catatan-card" data-section="sinkron-warning"><div class="catatan-icon">⚠️</div><div class="catatan-text">{{ session('warning') }}</div></div>
    @endif

    @if(session('error'))
    <div class="alert-error-bar" data-section="error"><span>⚠️</span> {{ session('error') }}</div>
    @endif

    @if($errors->any())
    <div class="alert-error-bar" data-section="validation-errors">
        <span>⚠️</span>
        <div>
            @foreach($errors->all() as $message)
                <div>{{ $message }}</div>
            @endforeach
        </div>
    </div>
    @endif

    @if($usulanSelesai)
    <div class="fu-info-banner" data-section="usulan-selesai">
        <div class="fu-info-icon">🎓</div>
        <div>
            <div class="fu-info-title">Usulan sebelumnya telah Selesai</div>
            <div class="fu-info-desc">Anda dapat mengajukan usulan baru melalui formulir di bawah ini.</div>
        </div>
    </div>
    @endif

    @if($isPerbaikan)
    <div class="catatan-card" data-section="perbaikan-banner">
        <div class="catatan-icon">🛠️</div>
        <div>
            <div class="catatan-title">Perbaikan: usulan Anda dinyatakan TMS</div>
            <div class="catatan-text">
                {{ $usulan->catatan ?: 'Perbaiki berkas yang ditandai, lalu klik "Kirim ulang".' }}
            </div>
        </div>
    </div>
    @endif

    @if(!$editable)
    {{-- ─── USULAN SEDANG DIPROSES (READ ONLY) ─── --}}
    <div class="pb-hero" data-section="usulan-diproses">
        <div class="pb-hero-top">
            <div>
                <div class="pb-hero-label">Usulan Sedang Diproses</div>
                <div class="pb-hero-name">{{ $usulan->nama_lengkap }}</div>
                <div class="pb-hero-meta">
                    <div class="pb-hero-chip">🎓 {{ $usulan->jabatan_fungsional }}</div>
                    <div class="pb-hero-chip">📋 {{ $usulan->jenis_usulan }}</div>
                </div>
            </div>
            <div class="pb-badge-aktif">● {{ $usulan->statusKeyLabel() }}</div>
        </div>
        <div style="font-size:13px; color:rgba(255,255,255,0.85);">
            Data dan berkas tidak dapat diubah selama usulan diproses.
            Pantau perkembangannya di menu <a href="{{ route('pelacakan-status') }}" style="color:#fde047; font-weight:700;">Pelacakan Status</a>.
        </div>
    </div>

    <div class="pb-detail-card">
        <div class="pb-detail-header">📎 Berkas Persyaratan</div>
        <div class="fu-card-body">
            <div class="slot-list">
                @foreach($persyaratan as $key => $label)
                    @php $file = $berkas->get($key); @endphp
                    <div class="slot-item">
                        <div>
                            <div class="slot-title">{{ $label }}</div>
                            <div class="slot-status {{ $file ? 'ok' : '' }}">{{ $file ? '✔ ' . $file->original_name : 'Belum diunggah' }}</div>
                        </div>
                        @if($file)
                        <div class="slot-actions">
                            <a href="{{ route('files.show', ['type' => 'berkas', 'id' => $file->id, 'mode' => 'preview']) }}" target="_blank" rel="noopener">👁 Preview</a>
                            <a href="{{ route('files.show', ['type' => 'berkas', 'id' => $file->id]) }}">⬇ Unduh</a>
                        </div>
                        @endif
                    </div>
                @endforeach
            </div>
            @include('modules.partials.legacy-links', ['usulan' => $usulan])
        </div>
    </div>

    @else
    {{-- ─── SINKRON DATA SIMPEG/SIASN (FR-PU-1) ─── --}}
    <form action="{{ route('pengajuan.sinkron') }}" method="POST" class="fu-card animate-fade-in" style="margin-bottom:16px;" data-form="sinkron-simpeg">
        @csrf
        <div class="fu-card-header" style="justify-content: space-between;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div class="fu-card-header-icon" style="background:#f0fdf4;">🔄</div>
                <div>
                    <div class="fu-card-header-title">Sinkron Data dengan SIMPEG/SIASN</div>
                    <div class="fu-card-header-subtitle">Perbarui nama, jabatan, dan unit kerja dari SIMPEG/SIASN</div>
                </div>
            </div>
            <button type="submit" class="fu-btn-secondary" data-action="sinkron-simpeg">🔄 Sinkron Data</button>
        </div>
    </form>

    {{-- ─── FORM ENTRI DATA + UPLOAD BERKAS ─── --}}
    <form action="{{ route('pengajuan.store') }}" method="POST" enctype="multipart/form-data" id="formPengajuan">
        @csrf

        {{-- ENTRI DATA --}}
        <div class="fu-card animate-fade-in" style="margin-bottom:16px;">
            <div class="fu-card-header">
                <div class="fu-card-header-icon" style="background:#eff6ff;">👤</div>
                <div>
                    <div class="fu-card-header-title">Entri Data</div>
                    <div class="fu-card-header-subtitle">Identitas diambil dari akun Anda; lengkapi data usulan</div>
                </div>
            </div>
            <div class="fu-card-body">
                <div class="readonly-identity">
                    <div class="fu-field">
                        <label class="fu-label">Nama Lengkap</label>
                        <input type="text" class="fu-input" value="{{ $authUser->name }}" readonly>
                    </div>
                    <div class="fu-field">
                        <label class="fu-label">NIP</label>
                        <input type="text" class="fu-input" value="{{ $authUser->nip }}" readonly>
                    </div>
                    <div class="fu-field">
                        <label class="fu-label">Email</label>
                        <input type="text" class="fu-input" value="{{ $authUser->email }}" readonly>
                    </div>
                    <div class="fu-field">
                        <label class="fu-label">Jabatan</label>
                        <input type="text" class="fu-input" value="{{ $authUser->jabatan }}" readonly data-field="jabatan">
                    </div>
                </div>

                <div class="fu-grid">
                    <div class="fu-field">
                        <label class="fu-label">Nomor Surat <span class="req">*</span></label>
                        <input type="text" name="nomor_surat" class="fu-input @error('nomor_surat') is-invalid @enderror"
                            placeholder="Contoh: 001/BPS-RI/SDM/X/2026"
                            value="{{ old('nomor_surat', $usulan?->nomor_surat) }}" required>
                        @error('nomor_surat')<div class="fu-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="fu-field">
                        <label class="fu-label">Hal Surat <span class="req">*</span></label>
                        <input type="text" name="hal_surat" class="fu-input @error('hal_surat') is-invalid @enderror"
                            placeholder="Contoh: Permohonan Uji Kompetensi Kenaikan Jenjang"
                            value="{{ old('hal_surat', $usulan?->hal_surat) }}" required>
                        @error('hal_surat')<div class="fu-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="fu-field fu-grid-full">
                        <label class="fu-label">Satuan Kerja <span class="req">*</span></label>
                        <input type="text" name="satuan_kerja" class="fu-input @error('satuan_kerja') is-invalid @enderror"
                            placeholder="Contoh: BPS Provinsi DKI Jakarta"
                            value="{{ old('satuan_kerja', $usulan?->satuan_kerja ?? $authUser->unit_kerja) }}" required>
                        @error('satuan_kerja')<div class="fu-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="fu-field">
                        <label class="fu-label">Jabatan Fungsional <span class="req">*</span></label>
                        @php $jfValue = old('jabatan_fungsional', $usulan?->jabatan_fungsional); @endphp
                        <select name="jabatan_fungsional" class="fu-select @error('jabatan_fungsional') is-invalid @enderror" required>
                            <option value="" disabled {{ $jfValue ? '' : 'selected' }}>-- Pilih Jabatan Fungsional --</option>
                            @foreach($jabfungList as $jf)
                            <option value="{{ $jf }}" {{ $jfValue === $jf ? 'selected' : '' }}>{{ $jf }}</option>
                            @endforeach
                        </select>
                        @error('jabatan_fungsional')<div class="fu-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="fu-field">
                        <label class="fu-label">Jenis Usulan <span class="req">*</span></label>
                        @php $juValue = old('jenis_usulan', $usulan?->jenis_usulan); @endphp
                        <select name="jenis_usulan" class="fu-select @error('jenis_usulan') is-invalid @enderror" required>
                            <option value="" disabled {{ $juValue ? '' : 'selected' }}>-- Pilih Jenis Usulan --</option>
                            @foreach($jenisUsulanList as $ju)
                            <option value="{{ $ju }}" {{ $juValue === $ju ? 'selected' : '' }}>{{ $ju }}</option>
                            @endforeach
                        </select>
                        @error('jenis_usulan')<div class="fu-error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- UPLOAD BERKAS PERSYARATAN --}}
        <div class="fu-card animate-fade-in">
            <div class="fu-card-header">
                <div class="fu-card-header-icon" style="background:#fff7ed;">📎</div>
                <div>
                    <div class="fu-card-header-title">Upload Berkas Persyaratan</div>
                    <div class="fu-card-header-subtitle">Format PDF, maksimal {{ $maxKb / 1024 }} MB per berkas. Berkas dapat diunggah bertahap lalu disimpan.</div>
                </div>
            </div>
            <div class="fu-card-body">
                <div class="slot-list">
                    @foreach($persyaratan as $key => $label)
                        @php
                            $file = $berkas->get($key);
                            $flagged = $file && $file->pemeriksaan === \App\Models\UsulanBerkas::PEMERIKSAAN_PERLU_PERBAIKAN;
                        @endphp
                        <div class="slot-item {{ $flagged ? 'is-flagged' : '' }}" data-slot="{{ $key }}">
                            <div>
                                <div class="slot-title">{{ $loop->iteration }}. {{ $label }} <span class="req" style="color:#ef4444;">*</span></div>
                                <div class="slot-status {{ $file ? 'ok' : '' }}">{{ $file ? '✔ ' . $file->original_name : 'Belum diunggah' }}</div>
                                @if($flagged)
                                <div class="slot-flag-note">⚠️ Perlu perbaikan{{ $file->catatan ? ': ' . $file->catatan : '' }}</div>
                                @endif
                                @error('berkas.' . $key)<div class="fu-error">{{ $message }}</div>@enderror
                            </div>
                            @if($file)
                            <div class="slot-actions">
                                <a href="{{ route('files.show', ['type' => 'berkas', 'id' => $file->id, 'mode' => 'preview']) }}" target="_blank" rel="noopener">👁 Preview</a>
                                <a href="{{ route('files.show', ['type' => 'berkas', 'id' => $file->id]) }}">⬇ Unduh</a>
                            </div>
                            @endif
                            <input type="file" name="berkas[{{ $key }}]" accept="application/pdf,.pdf" class="slot-file">
                        </div>
                    @endforeach
                </div>

                <div class="fu-submit-row">
                    <button type="submit" name="aksi" value="simpan" class="fu-btn-secondary">💾 Simpan</button>
                    @unless($isPerbaikan)
                    <button type="submit" name="aksi" value="kirim" class="fu-btn-submit" data-action="kirim-usulan">
                        <span>📤</span><span>Kirim Usulan</span>
                    </button>
                    @endunless
                </div>
            </div>
        </div>
    </form>

    @if($isPerbaikan)
    <form action="{{ route('pengajuan.kirim-ulang') }}" method="POST" style="display:flex; justify-content:flex-end;">
        @csrf
        <button type="submit" class="fu-btn-submit" data-action="kirim-ulang">
            <span>🔁</span><span>Kirim Ulang</span>
        </button>
    </form>
    @endif

    @if($usulan)
        @include('modules.partials.legacy-links', ['usulan' => $usulan])
    @endif
    @endif

</div>
@endsection
