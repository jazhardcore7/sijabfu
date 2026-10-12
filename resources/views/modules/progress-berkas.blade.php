@extends('layouts.app')

@section('title', 'Progress Berkas Saya')
@section('breadcrumb', 'Progress Berkas')

@push('styles')
<style>
    /* ─── HERO CARD ─── */
    .pb-hero {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
        border-radius: 24px;
        padding: 36px 40px;
        color: white;
        margin-bottom: 32px;
        position: relative;
        overflow: hidden;
    }
    .pb-hero::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 240px; height: 240px;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
    }
    .pb-hero::after {
        content: '';
        position: absolute;
        bottom: -40px; left: 40%;
        width: 160px; height: 160px;
        border-radius: 50%;
        background: rgba(255,255,255,0.04);
    }
    .pb-hero-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 28px;
    }
    .pb-hero-label {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: rgba(255,255,255,0.6);
        margin-bottom: 6px;
    }
    .pb-hero-nomor {
        font-size: 26px;
        font-weight: 900;
        letter-spacing: 0.5px;
    }
    .pb-hero-meta {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 8px;
    }
    .pb-hero-chip {
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 8px;
        padding: 5px 14px;
        font-size: 13px;
        font-weight: 600;
    }
    .pb-badge-status {
        padding: 8px 18px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 700;
    }
    .pb-badge-aktif { background: rgba(74,222,128,0.2); color: #4ade80; border: 1px solid rgba(74,222,128,0.3); }

    /* ─── PROGRESS BAR ─── */
    .pb-progress-section { }
    .pb-progress-label {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        font-weight: 600;
        color: rgba(255,255,255,0.8);
        margin-bottom: 10px;
    }
    .pb-progress-bar-track {
        background: rgba(255,255,255,0.12);
        border-radius: 50px;
        height: 12px;
        overflow: hidden;
    }
    .pb-progress-bar-fill {
        height: 100%;
        border-radius: 50px;
        background: linear-gradient(90deg, #4ade80, #22d3ee);
        transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    .pb-progress-bar-fill::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        animation: shimmer 2s infinite;
    }
    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    /* ─── STAGES GRID ─── */
    .stages-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
        margin-bottom: 32px;
    }

    .stage-card {
        background: white;
        border-radius: 16px;
        padding: 20px 22px;
        border: 2px solid #e2e8f0;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .stage-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0;
        width: 4px; height: 100%;
        border-radius: 0 4px 4px 0;
    }
    .stage-card.status-done    { border-color: #bbf7d0; }
    .stage-card.status-done::before    { background: #16a34a; }
    .stage-card.status-process { border-color: #fed7aa; }
    .stage-card.status-process::before { background: #f97316; }
    .stage-card.status-pending { border-color: #e2e8f0; }
    .stage-card.status-pending::before { background: #cbd5e1; }
    .stage-card.status-rejected{ border-color: #fecaca; }
    .stage-card.status-rejected::before { background: #dc2626; }

    .stage-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,0.1); }

    .stage-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    .stage-icon {
        width: 44px; height: 44px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
    }
    .stage-icon.bg-done     { background: #dcfce7; }
    .stage-icon.bg-process  { background: #ffedd5; }
    .stage-icon.bg-pending  { background: #f1f5f9; }
    .stage-icon.bg-rejected { background: #fee2e2; }

    .stage-step-num {
        font-size: 11px;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .stage-title {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 6px;
    }

    /* ─── STATUS BADGES ─── */
    .badge-done     { background: #dcfce7; color: #16a34a; padding: 4px 12px; border-radius: 50px; font-size: 12px; font-weight: 700; }
    .badge-process  { background: #ffedd5; color: #ea580c; padding: 4px 12px; border-radius: 50px; font-size: 12px; font-weight: 700; }
    .badge-pending  { background: #f1f5f9; color: #64748b; padding: 4px 12px; border-radius: 50px; font-size: 12px; font-weight: 700; }
    .badge-rejected { background: #fee2e2; color: #dc2626; padding: 4px 12px; border-radius: 50px; font-size: 12px; font-weight: 700; }

    /* ─── TIMELINE MINI ─── */
    .mini-timeline {
        display: flex;
        align-items: center;
        gap: 0;
        margin-bottom: 32px;
        overflow-x: auto;
        padding-bottom: 8px;
    }
    .mini-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        min-width: 80px;
        position: relative;
    }
    .mini-step:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 18px;
        left: 50%;
        width: 100%;
        height: 3px;
        background: #e2e8f0;
        z-index: 0;
    }
    .mini-step.done::after   { background: #16a34a; }
    .mini-step.process::after { background: linear-gradient(90deg, #f97316, #e2e8f0); }

    .mini-dot {
        width: 36px; height: 36px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px;
        position: relative;
        z-index: 1;
        border: 3px solid #e2e8f0;
        background: white;
        transition: all 0.3s;
    }
    .mini-step.done    .mini-dot { background: #16a34a; border-color: #16a34a; color: white; }
    .mini-step.process .mini-dot { background: #f97316; border-color: #f97316; color: white; animation: pulse-dot 1.5s infinite; }
    .mini-step.pending .mini-dot { background: white; border-color: #cbd5e1; color: #94a3b8; }
    .mini-step.rejected .mini-dot { background: #dc2626; border-color: #dc2626; color: white; }

    @keyframes pulse-dot {
        0%, 100% { box-shadow: 0 0 0 0 rgba(249,115,22,0.4); }
        50%       { box-shadow: 0 0 0 8px rgba(249,115,22,0); }
    }

    .mini-label {
        font-size: 10px;
        font-weight: 600;
        color: #64748b;
        text-align: center;
        margin-top: 8px;
        max-width: 72px;
        line-height: 1.3;
    }
    .mini-step.done    .mini-label { color: #16a34a; }
    .mini-step.process .mini-label { color: #f97316; }

    /* ─── CATATAN CARD ─── */
    .catatan-card {
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 16px;
        padding: 20px 24px;
        margin-bottom: 24px;
        display: flex;
        gap: 14px;
        align-items: flex-start;
    }
    .catatan-icon { font-size: 22px; flex-shrink: 0; margin-top: 2px; }
    .catatan-title { font-weight: 700; color: #92400e; font-size: 14px; margin-bottom: 4px; }
    .catatan-text  { font-size: 14px; color: #78350f; line-height: 1.7; }

    /* ─── EMPTY STATE ─── */
    .empty-state {
        text-align: center;
        padding: 60px 24px;
        background: white;
        border-radius: 20px;
        border: 2px dashed #e2e8f0;
    }
    .empty-state-icon { font-size: 60px; margin-bottom: 20px; }
    .empty-state h3 { font-size: 22px; font-weight: 800; color: #1e293b; margin-bottom: 10px; }
    .empty-state p  { font-size: 15px; color: #64748b; max-width: 420px; margin: 0 auto 24px; line-height: 1.7; }
    .btn-primary-link {
        background: linear-gradient(135deg, var(--bps-dark), var(--bps-mid));
        color: white;
        padding: 12px 28px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 700;
        display: inline-block;
        transition: all 0.2s;
    }
    .btn-primary-link:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,59,115,0.3); }
</style>
@endpush

@section('content')
<!-- PAGE HEADER -->
<div class="page-header animate-fade-in">
    <h1 class="page-header-title">📊 Progress Berkas Saya</h1>
    <p class="page-header-desc">Pantau posisi berkas usulan jabatan fungsional Anda secara real-time</p>
    <div class="page-header-divider"></div>
</div>

@if($berkas)

{{-- ─── HERO CARD ─── --}}
<div class="pb-hero animate-fade-in animate-delay-1">
    <div class="pb-hero-top">
        <div>
            <div class="pb-hero-label">Nomor Usulan</div>
            <div class="pb-hero-nomor">{{ $berkas->nomor_usulan }}</div>
            <div class="pb-hero-meta">
                <div class="pb-hero-chip">🎓 {{ $berkas->jenis_jabatan }}</div>
                <div class="pb-hero-chip">📊 {{ $berkas->jenjang }}</div>
                @if($berkas->tanggal_usulan)
                <div class="pb-hero-chip">📅 Diajukan: {{ $berkas->tanggal_usulan->format('d M Y') }}</div>
                @endif
            </div>
        </div>
        <div class="pb-badge-status pb-badge-aktif">● Aktif</div>
    </div>

    {{-- Progress Bar --}}
    <div class="pb-progress-section">
        <div class="pb-progress-label">
            <span>Progres Keseluruhan</span>
            <span>{{ $persen }}%</span>
        </div>
        <div class="pb-progress-bar-track">
            <div class="pb-progress-bar-fill" style="width: {{ $persen }}%;"></div>
        </div>
    </div>
</div>

{{-- ─── MINI TIMELINE ─── --}}
<div class="mini-timeline animate-fade-in animate-delay-1" style="background:white; border-radius:16px; padding:20px 24px; margin-bottom:24px; border:1px solid #e2e8f0;">
    @foreach($tahapan as $i => $t)
    @php
        $st = $berkas->{$t['key']};
        $cls = match($st) { 2 => 'done', 1 => 'process', 3 => 'rejected', default => 'pending' };
        $icon = match($st) { 2 => '✓', 1 => '●', 3 => '✕', default => ($i+1) };
    @endphp
    <div class="mini-step {{ $cls }}">
        <div class="mini-dot">{{ $icon }}</div>
        <div class="mini-label">{{ $t['label'] }}</div>
    </div>
    @endforeach
</div>

{{-- ─── CATATAN ADMIN ─── --}}
@if($berkas->catatan)
<div class="catatan-card animate-fade-in animate-delay-2">
    <div class="catatan-icon">📋</div>
    <div>
        <div class="catatan-title">Catatan dari Tim Pengelola</div>
        <div class="catatan-text">{{ $berkas->catatan }}</div>
    </div>
</div>
@endif

{{-- ─── STAGES GRID ─── --}}
<div class="stages-grid">
    @foreach($tahapan as $i => $t)
    @php
        $st = $berkas->{$t['key']};
        $cardCls  = match($st) { 2 => 'status-done', 1 => 'status-process', 3 => 'status-rejected', default => 'status-pending' };
        $iconBg   = match($st) { 2 => 'bg-done', 1 => 'bg-process', 3 => 'bg-rejected', default => 'bg-pending' };
        $badgeCls = \App\Models\ProgressBerkas::badgeStatus($st);
        $label    = \App\Models\ProgressBerkas::labelStatus($st);
    @endphp
    <div class="stage-card {{ $cardCls }} animate-fade-in animate-delay-{{ $i + 1 }}">
        <div class="stage-card-top">
            <div class="stage-icon {{ $iconBg }}">{{ $t['icon'] }}</div>
            <span class="stage-step-num">Tahap {{ str_pad($i+1, 2, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="stage-title">{{ $t['label'] }}</div>
        <span class="{{ $badgeCls }}">{{ $label }}</span>
    </div>
    @endforeach
</div>

{{-- ─── INFO BANTUAN ─── --}}
<div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 16px; padding: 20px 24px; max-width: 100%;" class="animate-fade-in">
    <div style="display:flex; gap:14px; align-items:flex-start;">
        <span style="font-size:22px; flex-shrink:0;">💬</span>
        <div>
            <div style="font-weight:700; color:var(--bps-dark); margin-bottom:6px; font-size:14px;">Ada pertanyaan?</div>
            <div style="font-size:13.5px; color:#374151; line-height:1.7;">
                Jika ada pertanyaan terkait status berkas Anda, silakan hubungi Tim Kerja Pembinaan JF melalui halaman
                <a href="{{ route('tim-kerja') }}" style="color:var(--bps-mid); font-weight:600;">Tim Kerja</a>.
                Informasi status diperbarui oleh admin secara berkala.
            </div>
        </div>
    </div>
</div>

@else

{{-- ─── EMPTY STATE ─── --}}
<div class="empty-state animate-fade-in">
    <div class="empty-state-icon">📭</div>
    <h3>Belum Ada Berkas Usulan</h3>
    <p>
        Anda belum memiliki berkas usulan jabatan fungsional yang terdaftar.
        Silakan ajukan usulan terlebih dahulu melalui Formulir Usulan.
    </p>
    <a href="{{ route('form-usulan') }}" class="btn-primary-link">
        📝 Buka Formulir Usulan
    </a>
</div>

@endif
@endsection
