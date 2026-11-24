@extends('layouts.app')

@section('title', 'Form Usulan & Progress')
@section('breadcrumb', 'Usulan')

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
    .fu-card-header-title { font-size: 14.5px; font-weight: 700; color: var(--bps-dark); }
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
</style>
@endpush

@section('content')
{{-- PAGE HEADER --}}
<div class="page-header animate-fade-in">
    @if($sudahSubmit)
        <h1 class="page-header-title">📊 Progress Usulan Berkas</h1>
        <p class="page-header-desc">Pantau proses pengusulan jabatan fungsional Anda secara real-time</p>
    @else
        <h1 class="page-header-title">📝 Form Usulan SIGMA JF</h1>
        <p class="page-header-desc">Ajukan usulan jabatan fungsional Anda secara online melalui portal SIGMA JF</p>
    @endif
    <div class="page-header-divider"></div>
</div>

<div class="fu-wrapper">

    @if(session('success'))
    <div class="alert-success-bar animate-fade-in">
        <span>✅</span> {{ session('success') }}
    </div>
    @endif

    @if($sudahSubmit && $usulan)
    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- SUDAH MENGISI FORM: TAMPILKAN PROGRESS BERKAS (LENGKAP)  --}}
    {{-- FORM DIKUNCI / TIDAK DAPAT DIISI LAGI                   --}}
    {{-- ══════════════════════════════════════════════════════════ --}}

    {{-- LOCK NOTICE BADGE --}}
    <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:12px; padding:12px 18px; display:flex; align-items:center; gap:10px; color:#166534; font-size:13px; font-weight:600;">
        <span>🔒</span>
        <span>Anda sudah mengisi formulir usulan. Formulir telah dikunci dan status progres saat ini dikelola oleh Tim Pembinaan JF (Admin).</span>
    </div>

    {{-- HERO CARD --}}
    <div class="pb-hero animate-fade-in">
        <div class="pb-hero-top">
            <div>
                <div class="pb-hero-label">Pegawai Pengusul</div>
                <div class="pb-hero-name">{{ $usulan->nama_lengkap }}</div>
                <div class="pb-hero-meta">
                    <div class="pb-hero-chip">🎓 {{ $usulan->jabatan_fungsional }}</div>
                    <div class="pb-hero-chip">📋 {{ $usulan->jenis_usulan }}</div>
                    <div class="pb-hero-chip">📅 Diajukan: {{ $usulan->created_at->format('d M Y') }}</div>
                </div>
            </div>
            <div class="pb-badge-aktif">● {{ $usulan->statusLabel() }}</div>
        </div>
        <div class="pb-progress-label">
            <span>Progress Alur Usulan</span>
            <span>Tahap {{ $usulan->status }} dari 5 ({{ $persen }}%)</span>
        </div>
        <div class="pb-progress-track">
            <div class="pb-progress-fill" style="width: {{ $persen }}%;"></div>
        </div>
    </div>

    {{-- CATATAN ADMIN --}}
    @if($usulan->catatan)
    <div class="catatan-card animate-fade-in">
        <div class="catatan-icon">📋</div>
        <div>
            <div class="catatan-title">Catatan dari Tim Pembinaan JF:</div>
            <div class="catatan-text">{{ $usulan->catatan }}</div>
        </div>
    </div>
    @endif

    {{-- SWIMLANE 5 TAHAPAN (ATTACHMENT 1) --}}
    <div class="pb-swimlane animate-fade-in">
        <div class="pb-swimlane-title">
            <span>🏊 Alur & Status Tahapan Usulan</span>
            <span style="font-size:12px; font-weight:600; color:var(--c-text-muted);">Disesuaikan oleh Tim Pembinaan JF</span>
        </div>
        <div class="pb-lanes">
            @foreach($tahapan as $i => $t)
            @php
                $step     = $t['step'];
                $current  = $usulan->status;
                $state    = $current > $step ? 'done' : ($current === $step ? 'active' : 'pending');
                $numIcon  = $state === 'done' ? '✓' : ($step);
                $badgeTxt = $state === 'done' ? 'Selesai' : ($state === 'active' ? 'Sedang Berjalan' : 'Menunggu');
            @endphp
            <div class="pb-lane">
                <div class="pb-lane-actor">
                    <div class="pb-lane-actor-badge" style="background: {{ $t['color'] }}22;">{{ $t['icon'] }}</div>
                    <div class="pb-lane-actor-name">{{ $t['aktor'] }}</div>
                </div>
                <div class="pb-lane-step">
                    <div class="pb-step-num {{ $state }}">{{ $numIcon }}</div>
                    <div class="pb-step-content">
                        <div class="pb-step-label">{{ $step }}. {{ $t['label'] }}</div>
                        <div class="pb-step-desc">{{ $t['desc'] }}</div>
                    </div>
                    <div class="pb-step-badge {{ $state }}">{{ $badgeTxt }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- DETAIL USULAN --}}
    <div class="pb-detail-card animate-fade-in">
        <div class="pb-detail-header">
            <span>📄 Data Dokumen Usulan yang Diajukan</span>
            <span style="font-size:12px; color:var(--c-text-muted);">No. Surat: {{ $usulan->nomor_surat }}</span>
        </div>
        <div class="pb-detail-grid">
            <div class="pb-detail-row">
                <div class="pb-detail-label">Nama Lengkap</div>
                <div class="pb-detail-value">{{ $usulan->nama_lengkap }}</div>
            </div>
            <div class="pb-detail-row">
                <div class="pb-detail-label">NIP</div>
                <div class="pb-detail-value">{{ $usulan->nip }}</div>
            </div>
            <div class="pb-detail-row">
                <div class="pb-detail-label">Jabatan Fungsional</div>
                <div class="pb-detail-value">{{ $usulan->jabatan_fungsional }}</div>
            </div>
            <div class="pb-detail-row">
                <div class="pb-detail-label">Jenis Usulan</div>
                <div class="pb-detail-value">{{ $usulan->jenis_usulan }}</div>
            </div>
            <div class="pb-detail-row">
                <div class="pb-detail-label">Satuan Kerja</div>
                <div class="pb-detail-value">{{ $usulan->satuan_kerja }}</div>
            </div>
            <div class="pb-detail-row">
                <div class="pb-detail-label">Email</div>
                <div class="pb-detail-value">{{ $usulan->email }}</div>
            </div>
            <div class="pb-detail-row">
                <div class="pb-detail-label">Hal Surat</div>
                <div class="pb-detail-value">{{ $usulan->hal_surat }}</div>
            </div>
            <div class="pb-detail-row">
                <div class="pb-detail-label">Waktu Pengajuan</div>
                <div class="pb-detail-value">{{ $usulan->created_at->format('d F Y, H:i') }} WIB</div>
            </div>
            <div class="pb-detail-row" style="grid-column: 1 / -1;">
                <div class="pb-detail-label">Link Berkas (Laci BPS / Google Drive)</div>
                <a href="{{ $usulan->link_berkas }}" target="_blank" class="pb-detail-link">{{ $usulan->link_berkas }} ↗</a>
            </div>
            @if($usulan->link_usulan)
            <div class="pb-detail-row" style="grid-column: 1 / -1;">
                <div class="pb-detail-label">Link Usulan Tambahan</div>
                <a href="{{ $usulan->link_usulan }}" target="_blank" class="pb-detail-link">{{ $usulan->link_usulan }} ↗</a>
            </div>
            @endif
        </div>
    </div>

    @else
    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- BELUM MENGISI FORM: TAMPILKAN FORM LENGKAP               --}}
    {{-- ══════════════════════════════════════════════════════════ --}}

    {{-- INFO BANNER --}}
    <div class="fu-info-banner animate-fade-in">
        <div class="fu-info-icon">📋</div>
        <div>
            <div class="fu-info-title">Formulir Usulan Pembinaan Jabatan Fungsional</div>
            <div class="fu-info-desc">
                Isi formulir ini dengan data yang lengkap dan benar. Setelah dikirim, Anda dapat memantau
                proses berkas secara langsung di halaman ini. <strong>Formulir hanya dapat dikirim 1 (satu) kali.</strong>
            </div>
        </div>
    </div>

    {{-- FORM --}}
    <form action="{{ route('form-usulan.store') }}" method="POST" id="formUsulan">
        @csrf

        {{-- SECTION 1: IDENTITAS SURAT --}}
        <div class="fu-card animate-fade-in" style="margin-bottom:16px;">
            <div class="fu-card-header">
                <div class="fu-card-header-icon" style="background:#eff6ff;">📄</div>
                <div>
                    <div class="fu-card-header-title">Identitas Surat</div>
                    <div class="fu-card-header-subtitle">Informasi surat pengantar usulan</div>
                </div>
            </div>
            <div class="fu-card-body">
                <div class="fu-grid">
                    <div class="fu-field">
                        <label class="fu-label">Nomor Surat <span class="req">*</span></label>
                        <input type="text" name="nomor_surat" class="fu-input @error('nomor_surat') is-invalid @enderror"
                            placeholder="Contoh: 001/BPS-RI/SDM/X/2026"
                            value="{{ old('nomor_surat') }}" required>
                        @error('nomor_surat')<div class="fu-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="fu-field">
                        <label class="fu-label">Hal Surat <span class="req">*</span></label>
                        <input type="text" name="hal_surat" class="fu-input @error('hal_surat') is-invalid @enderror"
                            placeholder="Contoh: Permohonan Uji Kompetensi Kenaikan Jenjang"
                            value="{{ old('hal_surat') }}" required>
                        @error('hal_surat')<div class="fu-error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 2: DATA PEGAWAI --}}
        <div class="fu-card animate-fade-in" style="margin-bottom:16px;">
            <div class="fu-card-header">
                <div class="fu-card-header-icon" style="background:#f0fdf4;">👤</div>
                <div>
                    <div class="fu-card-header-title">Data Pegawai</div>
                    <div class="fu-card-header-subtitle">Identitas pejabat fungsional yang diusulkan</div>
                </div>
            </div>
            <div class="fu-card-body">
                <div class="fu-grid">
                    <div class="fu-field fu-grid-full">
                        <label class="fu-label">Nama Lengkap <span class="req">*</span></label>
                        <input type="text" name="nama_lengkap" class="fu-input @error('nama_lengkap') is-invalid @enderror"
                            placeholder="Nama lengkap beserta gelar"
                            value="{{ old('nama_lengkap', auth()->user()->name) }}" required>
                        @error('nama_lengkap')<div class="fu-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="fu-field">
                        <label class="fu-label">NIP <span class="req">*</span></label>
                        <input type="text" name="nip" class="fu-input @error('nip') is-invalid @enderror"
                            placeholder="18 digit NIP"
                            value="{{ old('nip', auth()->user()->nip) }}" required>
                        @error('nip')<div class="fu-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="fu-field">
                        <label class="fu-label">Email <span class="req">*</span></label>
                        <input type="email" name="email" class="fu-input @error('email') is-invalid @enderror"
                            placeholder="email@bps.go.id"
                            value="{{ old('email', auth()->user()->email) }}" required>
                        @error('email')<div class="fu-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="fu-field fu-grid-full">
                        <label class="fu-label">Satuan Kerja <span class="req">*</span></label>
                        <input type="text" name="satuan_kerja" class="fu-input @error('satuan_kerja') is-invalid @enderror"
                            placeholder="Contoh: BPS Provinsi DKI Jakarta"
                            value="{{ old('satuan_kerja', auth()->user()->unit_kerja) }}" required>
                        @error('satuan_kerja')<div class="fu-error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 3: JABATAN FUNGSIONAL & JENIS USULAN --}}
        <div class="fu-card animate-fade-in" style="margin-bottom:16px;">
            <div class="fu-card-header">
                <div class="fu-card-header-icon" style="background:#fdf4ff;">🎯</div>
                <div>
                    <div class="fu-card-header-title">Jenis Usulan</div>
                    <div class="fu-card-header-subtitle">Pilih jabatan fungsional (33 JF) dan peruntukan usulan</div>
                </div>
            </div>
            <div class="fu-card-body">
                <div class="fu-grid">
                    <div class="fu-field">
                        <label class="fu-label">Jabatan Fungsional <span class="req">*</span></label>
                        <select name="jabatan_fungsional" class="fu-select @error('jabatan_fungsional') is-invalid @enderror" required>
                            <option value="" disabled selected>-- Pilih Jabatan Fungsional (33 JF) --</option>
                            @foreach($jabfungList as $jf)
                            <option value="{{ $jf }}" {{ old('jabatan_fungsional') === $jf ? 'selected' : '' }}>
                                {{ $jf }}
                            </option>
                            @endforeach
                        </select>
                        @error('jabatan_fungsional')<div class="fu-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="fu-field">
                        <label class="fu-label">Jenis Usulan <span class="req">*</span></label>
                        <select name="jenis_usulan" class="fu-select @error('jenis_usulan') is-invalid @enderror" required>
                            <option value="" disabled selected>-- Pilih Jenis Usulan --</option>
                            @foreach($jenisUsulanList as $ju)
                            <option value="{{ $ju }}" {{ old('jenis_usulan') === $ju ? 'selected' : '' }}>
                                {{ $ju }}
                            </option>
                            @endforeach
                        </select>
                        @error('jenis_usulan')<div class="fu-error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 4: LINK BERKAS --}}
        <div class="fu-card animate-fade-in">
            <div class="fu-card-header">
                <div class="fu-card-header-icon" style="background:#fff7ed;">🔗</div>
                <div>
                    <div class="fu-card-header-title">Link Berkas & Dokumen</div>
                    <div class="fu-card-header-subtitle">Tautan penyimpanan berkas digital</div>
                </div>
            </div>
            <div class="fu-card-body">
                <div class="fu-link-info">
                    <strong>⚠️ WAJIB:</strong> Upload surat usulan Eselon II dan kelengkapan berkas dalam <strong>satu media penyimpanan Laci BPS / Google Drive</strong>, kemudian salin link tersebut di bawah ini.<br>
                    <span style="color:#b45309; font-weight:600;">Catatan: Pastikan Link berikut dapat diakses oleh Tim Pembinaan Jafung.</span>
                </div>
                <div class="fu-grid">
                    <div class="fu-field fu-grid-full">
                        <label class="fu-label">Link Berkas <span class="req">*</span></label>
                        <input type="url" name="link_berkas" class="fu-input @error('link_berkas') is-invalid @enderror"
                            placeholder="https://drive.google.com/... atau https://laci.bps.go.id/..."
                            value="{{ old('link_berkas') }}" required>
                        @error('link_berkas')<div class="fu-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="fu-field fu-grid-full">
                        <label class="fu-label">Link Usulan <span style="color:var(--c-text-faint); font-weight:400;">(Opsional)</span></label>
                        <input type="text" name="link_usulan" class="fu-input"
                            placeholder="Tautan usulan pelengkap bila ada"
                            value="{{ old('link_usulan') }}">
                    </div>
                </div>

                {{-- TOMBOL KIRIM --}}
                <div class="fu-submit-row">
                    <button type="submit" class="fu-btn-submit" id="btnSubmit">
                        <span>📤</span>
                        <span>Kirim Usulan Jabatan Fungsional</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
    @endif

</div>
@endsection
