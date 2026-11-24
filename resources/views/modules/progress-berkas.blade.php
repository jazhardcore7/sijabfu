@extends('layouts.app')

@section('title', 'Progress Berkas Saya')
@section('breadcrumb', 'Progress Berkas')

@push('styles')
<style>
    /* ─── HERO CARD ─── */
    .pb-hero {
        background: linear-gradient(135deg, #1e3a5f 0%, #003b73 50%, #005baa 100%);
        border-radius: 24px;
        padding: 36px 40px;
        color: white;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
    }
    .pb-hero::before {
        content: '';
        position: absolute; top: -60px; right: -60px;
        width: 240px; height: 240px;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
    }
    .pb-hero::after {
        content: '';
        position: absolute; bottom: -40px; left: 40%;
        width: 160px; height: 160px;
        border-radius: 50%;
        background: rgba(255,255,255,0.04);
    }
    .pb-hero-top {
        display: flex; align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap; gap: 16px;
        margin-bottom: 28px;
    }
    .pb-hero-label {
        font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        color: rgba(255,255,255,0.6); margin-bottom: 4px;
    }
    .pb-hero-name { font-size: 22px; font-weight: 900; }
    .pb-hero-meta { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 8px; }
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
        transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .pb-progress-fill::after {
        content: '';
        position: absolute; top: 0; left: -100%;
        width: 100%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        animation: shimmer 2s infinite;
    }
    @keyframes shimmer { to { left: 100%; } }

    /* ─── SWIMLANE TIMELINE ─── */
    .pb-swimlane {
        background: var(--c-surface);
        border-radius: 20px;
        border: 1px solid var(--c-border);
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        margin-bottom: 24px;
    }
    .pb-swimlane-title {
        background: var(--c-surface-2);
        border-bottom: 1px solid var(--c-border);
        padding: 16px 24px;
        font-size: 14px; font-weight: 700; color: var(--c-text);
        display: flex; align-items: center; gap: 10px;
    }
    .pb-lanes { padding: 24px; display: flex; flex-direction: column; gap: 0; }
    .pb-lane {
        display: grid;
        grid-template-columns: 220px 1fr;
        min-height: 90px;
        position: relative;
    }
    .pb-lane:not(:last-child) {
        border-bottom: 1px dashed var(--c-border);
    }
    .pb-lane-actor {
        display: flex; align-items: center;
        padding: 16px 20px 16px 0;
        gap: 12px;
        border-right: 2px solid var(--c-border-soft);
        margin-right: 20px;
    }
    .pb-lane-actor-badge {
        width: 40px; height: 40px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }
    .pb-lane-actor-name {
        font-size: 12.5px; font-weight: 700; color: var(--c-text-2); line-height: 1.4;
    }
    .pb-lane-step {
        display: flex; align-items: center;
        padding: 16px 0;
        gap: 14px;
    }
    .pb-step-num {
        width: 36px; height: 36px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; font-weight: 800; flex-shrink: 0;
        position: relative;
        z-index: 1;
    }
    .pb-step-num.done  { background: #d1fae5; color: #065f46; }
    .pb-step-num.active { background: #dbeafe; color: #1e40af; box-shadow: 0 0 0 4px rgba(59,130,246,0.15); animation: pulsering 1.8s ease-in-out infinite; }
    .pb-step-num.pending { background: var(--c-surface-3); color: var(--c-text-faint); }
    @keyframes pulsering {
        0%, 100% { box-shadow: 0 0 0 4px rgba(59,130,246,0.15); }
        50%       { box-shadow: 0 0 0 8px rgba(59,130,246,0.08); }
    }
    .pb-step-content { flex: 1; }
    .pb-step-label { font-size: 14px; font-weight: 700; color: var(--c-text); margin-bottom: 3px; }
    .pb-step-desc  { font-size: 12px; color: var(--c-text-muted); line-height: 1.5; }
    .pb-step-badge {
        font-size: 11.5px; font-weight: 700;
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
        padding: 18px 22px;
        display: flex; gap: 14px; align-items: flex-start;
        margin-bottom: 24px;
    }
    .catatan-icon { font-size: 22px; flex-shrink: 0; }
    .catatan-title { font-weight: 700; color: #92400e; margin-bottom: 4px; font-size: 13.5px; }
    .catatan-text  { font-size: 13px; color: #78350f; line-height: 1.6; }

    /* ─── RINGKASAN USULAN ─── */
    .pb-detail-card {
        background: var(--c-surface);
        border-radius: 20px;
        border: 1px solid var(--c-border);
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        margin-bottom: 24px;
    }
    .pb-detail-header {
        background: var(--c-surface-2);
        border-bottom: 1px solid var(--c-border);
        padding: 14px 24px;
        font-size: 14px; font-weight: 700; color: var(--c-text);
        display: flex; align-items: center; gap: 10px;
    }
    .pb-detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
    }
    .pb-detail-row {
        padding: 14px 24px;
        border-bottom: 1px solid var(--c-border-soft);
    }
    .pb-detail-row:nth-child(odd) { border-right: 1px solid var(--c-border-soft); }
    .pb-detail-label { font-size: 11px; color: var(--c-text-faint); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
    .pb-detail-value { font-size: 13.5px; font-weight: 600; color: var(--c-text); }
    .pb-detail-link  { font-size: 13px; color: #005baa; word-break: break-all; }

    /* ─── EMPTY STATE ─── */
    .empty-state {
        background: var(--c-surface);
        border-radius: 24px;
        border: 2px dashed var(--c-border-strong);
        padding: 60px 32px;
        text-align: center;
    }
    .empty-state-icon { font-size: 56px; margin-bottom: 20px; }
    .empty-state h3 { font-size: 20px; font-weight: 800; color: var(--c-text); margin-bottom: 10px; }
    .empty-state p { color: var(--c-text-muted); font-size: 14px; max-width: 400px; margin: 0 auto 24px; line-height: 1.6; }
    .btn-to-form {
        display: inline-flex; align-items: center; gap: 8px;
        background: linear-gradient(135deg, var(--bps-dark), #00509D);
        color: white; text-decoration: none;
        padding: 12px 28px; border-radius: 12px;
        font-weight: 700; font-size: 14px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-to-form:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,59,115,0.3); color: white; }

    /* ─── INFO BANTUAN ─── */
    .pb-help {
        background: #eff6ff; border: 1px solid #bfdbfe;
        border-radius: 16px; padding: 18px 24px;
        display: flex; gap: 14px; align-items: flex-start;
    }
    .pb-help a { color: var(--bps-mid); font-weight: 600; }

    @media (max-width: 640px) {
        .pb-hero { padding: 24px 20px; }
        .pb-lane { grid-template-columns: 1fr; }
        .pb-lane-actor { border-right: none; border-bottom: 1px solid var(--c-border-soft); margin-right: 0; padding-bottom: 10px; }
        .pb-detail-grid { grid-template-columns: 1fr; }
        .pb-detail-row:nth-child(odd) { border-right: none; }
    }
</style>
@endpush

@section('content')
{{-- PAGE HEADER --}}
<div class="page-header animate-fade-in">
    <h1 class="page-header-title">📊 Progress Berkas Saya</h1>
    <p class="page-header-desc">Pantau posisi berkas usulan jabatan fungsional Anda secara real-time</p>
    <div class="page-header-divider"></div>
</div>

@if(session('success'))
<div style="background:#d1fae5; border:1.5px solid #6ee7b7; border-radius:12px; padding:14px 18px; color:#065f46; font-weight:600; font-size:13.5px; display:flex; align-items:center; gap:10px; margin-bottom:20px;">
    <span>✅</span> {{ session('success') }}
</div>
@endif

@if($usulan)

{{-- ─── HERO CARD ─── --}}
<div class="pb-hero animate-fade-in">
    <div class="pb-hero-top">
        <div>
            <div class="pb-hero-label">Nama Pegawai</div>
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
        <span>Progress Keseluruhan</span>
        <span>{{ $persen }}%</span>
    </div>
    <div class="pb-progress-track">
        <div class="pb-progress-fill" style="width: {{ $persen }}%;"></div>
    </div>
</div>

{{-- ─── CATATAN ADMIN ─── --}}
@if($usulan->catatan)
<div class="catatan-card animate-fade-in">
    <div class="catatan-icon">📋</div>
    <div>
        <div class="catatan-title">Catatan dari Tim Pembinaan JF</div>
        <div class="catatan-text">{{ $usulan->catatan }}</div>
    </div>
</div>
@endif

{{-- ─── SWIMLANE PROGRESS ─── --}}
<div class="pb-swimlane animate-fade-in animate-delay-1">
    <div class="pb-swimlane-title">
        🏊 Alur Proses Usulan
    </div>
    <div class="pb-lanes">
        @foreach($tahapan as $i => $t)
        @php
            $step     = $t['step'];
            $current  = $usulan->status;
            $state    = $current > $step ? 'done' : ($current === $step ? 'active' : 'pending');
            $numIcon  = $state === 'done' ? '✓' : ($step);
            $badgeTxt = $state === 'done' ? 'Selesai' : ($state === 'active' ? 'Sedang Diproses' : 'Belum Mulai');
        @endphp
        <div class="pb-lane">
            <div class="pb-lane-actor">
                <div class="pb-lane-actor-badge" style="background: {{ $t['color'] }}22; font-size:20px;">{{ $t['icon'] }}</div>
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

{{-- ─── RINGKASAN USULAN ─── --}}
<div class="pb-detail-card animate-fade-in animate-delay-2">
    <div class="pb-detail-header">📄 Ringkasan Usulan</div>
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
            <div class="pb-detail-label">Nomor Surat</div>
            <div class="pb-detail-value">{{ $usulan->nomor_surat }}</div>
        </div>
        <div class="pb-detail-row">
            <div class="pb-detail-label">Hal Surat</div>
            <div class="pb-detail-value">{{ $usulan->hal_surat }}</div>
        </div>
        <div class="pb-detail-row" style="grid-column: 1 / -1;">
            <div class="pb-detail-label">Link Berkas</div>
            <a href="{{ $usulan->link_berkas }}" target="_blank" class="pb-detail-link">{{ $usulan->link_berkas }}</a>
        </div>
        @if($usulan->link_usulan)
        <div class="pb-detail-row" style="grid-column: 1 / -1;">
            <div class="pb-detail-label">Link Usulan</div>
            <a href="{{ $usulan->link_usulan }}" target="_blank" class="pb-detail-link">{{ $usulan->link_usulan }}</a>
        </div>
        @endif
    </div>
</div>

{{-- ─── INFO BANTUAN ─── --}}
<div class="pb-help animate-fade-in">
    <span style="font-size:22px; flex-shrink:0;">💬</span>
    <div>
        <div style="font-weight:700; color:var(--bps-dark); margin-bottom:6px; font-size:14px;">Ada pertanyaan?</div>
        <div style="font-size:13.5px; color:var(--c-text-2); line-height:1.7;">
            Jika ada pertanyaan terkait status berkas Anda, silakan hubungi Tim Kerja Pembinaan JF melalui halaman
            <a href="{{ route('tim-kerja') }}">Tim Kerja</a>.
            Informasi status diperbarui oleh admin secara berkala.
        </div>
    </div>
</div>

@else

{{-- ─── EMPTY STATE ─── --}}
<div class="empty-state animate-fade-in">
    <div class="empty-state-icon">📭</div>
    <h3>Belum Ada Berkas Usulan</h3>
    <p>
        Anda belum mengajukan usulan jabatan fungsional.
        Silakan isi formulir usulan terlebih dahulu untuk memulai proses.
    </p>
    <a href="{{ route('form-usulan') }}" class="btn-to-form">
        📝 Isi Formulir Usulan
    </a>
</div>

@endif
@endsection
