@extends('layouts.app')

@section('title', 'Pelacakan Status')
@section('breadcrumb', 'Pelacakan Status')

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

    /* ─── TAMBAHAN PELACAKAN STATUS ─── */
    .hasil-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 14px; border-radius: 20px;
        font-size: 12px; font-weight: 800; white-space: nowrap;
    }
    .hasil-badge.ms  { background: rgba(52,211,153,0.2); border: 1.5px solid rgba(52,211,153,0.5); color: #6ee7b7; }
    .hasil-badge.tms { background: rgba(248,113,113,0.2); border: 1.5px solid rgba(248,113,113,0.5); color: #fca5a5; }
    .pb-step-num.perbaikan, .pb-step-badge.perbaikan { background: #fef3c7; color: #92400e; }
    .doc-card {
        background: var(--c-surface);
        border: 1px solid var(--c-border);
        border-radius: 20px;
        padding: 20px 24px;
        margin-bottom: 24px;
        display: flex; flex-direction: column; gap: 14px;
    }
    .doc-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
    .doc-title { font-size: 14px; font-weight: 700; color: var(--c-text); }
    .doc-sub { font-size: 12.5px; color: var(--c-text-muted); }
    .doc-actions { display: flex; gap: 8px; }
    .doc-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 14px; border-radius: 10px;
        font-size: 12.5px; font-weight: 700; text-decoration: none;
        border: 1.5px solid var(--c-border-strong); color: var(--c-text); background: var(--c-surface-2);
    }
    .doc-btn.primary { background: linear-gradient(135deg, var(--bps-dark), var(--bps-mid)); color: #FFFFFF; border: none; }
    .doc-missing { font-size: 12.5px; font-weight: 600; color: #b45309; }
    .timeline { list-style: none; padding: 20px 24px; margin: 0; }
    .timeline li { display: grid; grid-template-columns: 150px 1fr; gap: 12px; padding: 10px 0; border-bottom: 1px dashed var(--c-border); }
    .timeline li:last-child { border-bottom: none; }
    .timeline-date { font-size: 12px; color: var(--c-text-muted); font-weight: 600; }
    .timeline-main { font-size: 13px; color: var(--c-text); }
    .timeline-actor { font-size: 11.5px; color: var(--c-text-faint); }
    .timeline-note { font-size: 12.5px; color: var(--c-text-2); margin-top: 3px; }
    .flagged-list { margin: 8px 0 0 18px; font-size: 13px; color: #78350f; }
    @media (max-width: 640px) {
        .timeline li { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="page-header animate-fade-in">
    <h1 class="page-header-title">📊 Pelacakan Status</h1>
    <p class="page-header-desc">Pantau status usulan, unduh surat usulan, dan lihat sertifikat UKOM Anda</p>
    <div class="page-header-divider"></div>
</div>

@if(session('success'))
<div style="background:#d1fae5; border:1.5px solid #6ee7b7; border-radius:12px; padding:14px 18px; color:#065f46; font-weight:600; font-size:13.5px; display:flex; align-items:center; gap:10px; margin-bottom:20px;">
    <span>✅</span> {{ session('success') }}
</div>
@endif

@if($usulan)

{{-- ─── HERO ─── --}}
<div class="pb-hero animate-fade-in" data-status-key="{{ $usulan->status_key }}">
    <div class="pb-hero-top">
        <div>
            <div class="pb-hero-label">Nama Pegawai</div>
            <div class="pb-hero-name">{{ $usulan->nama_lengkap }}</div>
            <div class="pb-hero-meta">
                <div class="pb-hero-chip">🎓 {{ $usulan->jabatan_fungsional }}</div>
                <div class="pb-hero-chip">📋 {{ $usulan->jenis_usulan }}</div>
                <div class="pb-hero-chip">📅 Dikirim: {{ ($usulan->submitted_at ?? $usulan->created_at)->format('d M Y') }}</div>
            </div>
        </div>
        <div style="display:flex; flex-direction:column; gap:8px; align-items:flex-end;">
            <div class="pb-badge-aktif" data-section="status-label">● {{ $usulan->statusKeyLabel() }}</div>
            @if($usulan->hasil_verifikasi)
            {{-- FR-PS-3 --}}
            <div class="hasil-badge {{ $usulan->hasil_verifikasi }}" data-hasil="{{ $usulan->hasil_verifikasi }}">
                Hasil Verifikasi: {{ strtoupper($usulan->hasil_verifikasi) }}
            </div>
            @endif
        </div>
    </div>
    <div class="pb-progress-label">
        <span>Progress Keseluruhan</span>
        <span>{{ $persen }}%</span>
    </div>
    <div class="pb-progress-track">
        <div class="pb-progress-fill" style="width: {{ $persen }}%;"></div>
    </div>
</div>

{{-- ─── PERBAIKAN (FR-PS-2) ─── --}}
@if($usulan->status_key === \App\Models\FormUsulan::STATUS_PERBAIKAN)
<div class="catatan-card animate-fade-in" data-section="perbaikan-banner">
    <div class="catatan-icon">🛠️</div>
    <div>
        <div class="catatan-title">Perbaikan diperlukan</div>
        <div class="catatan-text">{{ $usulan->catatan ?: 'Silakan perbaiki berkas yang ditandai.' }}</div>
        @if($flaggedBerkas->isNotEmpty())
        <ul class="flagged-list">
            @foreach($flaggedBerkas as $fb)
            <li>{{ $persyaratan[$fb->jenis_persyaratan] ?? $fb->jenis_persyaratan }}{{ $fb->catatan ? ': ' . $fb->catatan : '' }}</li>
            @endforeach
        </ul>
        @endif
        <div style="margin-top:10px;">
            <a href="{{ route('pengajuan') }}" class="btn-to-form" style="padding:8px 18px; font-size:13px;">🛠️ Perbaiki dan Kirim Ulang</a>
        </div>
    </div>
</div>
@elseif($usulan->catatan)
<div class="catatan-card animate-fade-in" data-section="catatan-terbaru">
    <div class="catatan-icon">📋</div>
    <div>
        <div class="catatan-title">Catatan dari Tim Pembinaan JF</div>
        <div class="catatan-text">{{ $usulan->catatan }}</div>
    </div>
</div>
@endif

{{-- ─── TRACKER (FR-PS-1) ─── --}}
<div class="pb-swimlane animate-fade-in animate-delay-1">
    <div class="pb-swimlane-title">🏊 Status Usulan</div>
    <div class="pb-lanes">
        @foreach($tahapan as $i => $t)
        @php
            if ($isSelesai || $i < $currentIndex) { $state = 'done'; }
            elseif ($i === $currentIndex) { $state = $usulan->status_key === 'perbaikan' ? 'perbaikan' : 'active'; }
            else { $state = 'pending'; }
            $badgeTxt = match ($state) {
                'done' => 'Selesai',
                'active' => 'Sedang Diproses',
                'perbaikan' => 'Perbaikan',
                default => 'Belum Mulai',
            };
        @endphp
        <div class="pb-lane" data-stage="{{ $t['key'] }}" data-state="{{ $state }}">
            <div class="pb-lane-actor">
                <div class="pb-step-num {{ $state }}">{{ $state === 'done' ? '✓' : $loop->iteration }}</div>
                <div class="pb-lane-actor-name">{{ $t['label'] }}</div>
            </div>
            <div class="pb-lane-step">
                <div class="pb-step-content">
                    <div class="pb-step-desc">{{ $t['desc'] }}</div>
                </div>
                <div class="pb-step-badge {{ $state }}">{{ $badgeTxt }}</div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ─── DOKUMEN HASIL (FR-PS-4, FR-PS-5) ─── --}}
@if($showSurat || $showSertifikat)
<div class="doc-card animate-fade-in">
    @if($showSurat)
    <div class="doc-row" data-section="surat-usulan">
        <div>
            <div class="doc-title">📄 Surat Usulan</div>
            <div class="doc-sub">Surat usulan UKOM yang diterbitkan Tim Pembinaan JF</div>
        </div>
        @if($usulan->surat_usulan_path)
        <div class="doc-actions">
            <a class="doc-btn" href="{{ route('files.show', ['type' => 'surat', 'id' => $usulan->id, 'mode' => 'preview']) }}" target="_blank" rel="noopener" data-action="preview-surat">👁 Preview</a>
            <a class="doc-btn primary" href="{{ route('files.show', ['type' => 'surat', 'id' => $usulan->id]) }}" data-action="download-surat">⬇ Unduh</a>
        </div>
        @else
        <div class="doc-missing">Dokumen belum diunggah</div>
        @endif
    </div>
    @endif

    @if($showSertifikat)
    <div class="doc-row" data-section="sertifikat-ukom">
        <div>
            <div class="doc-title">🎓 Sertifikat UKOM</div>
            @if($usulan->hasil_ukom)
            <div class="doc-sub" data-section="hasil-ukom">Hasil UKOM: {{ $usulan->hasil_ukom }}</div>
            @endif
        </div>
        @if($usulan->sertifikat_path)
        <div class="doc-actions">
            <a class="doc-btn" href="{{ route('files.show', ['type' => 'sertifikat', 'id' => $usulan->id, 'mode' => 'preview']) }}" target="_blank" rel="noopener" data-action="preview-sertifikat">👁 Preview</a>
            <a class="doc-btn primary" href="{{ route('files.show', ['type' => 'sertifikat', 'id' => $usulan->id]) }}" data-action="download-sertifikat">⬇ Unduh</a>
        </div>
        @else
        <div class="doc-missing">Dokumen belum diunggah</div>
        @endif
    </div>
    @endif
</div>
@endif

{{-- ─── TIMELINE (FR-PS-1) ─── --}}
<div class="pb-detail-card animate-fade-in animate-delay-2">
    <div class="pb-detail-header">🕒 Riwayat Status</div>
    @if($logs->isEmpty())
    <div style="padding:18px 24px; font-size:13px; color:var(--c-text-muted);">Belum ada riwayat perubahan status.</div>
    @else
    <ul class="timeline" data-section="timeline">
        @foreach($logs as $log)
        <li data-log-to="{{ $log->to_status }}">
            <div class="timeline-date">{{ $log->created_at->format('d M Y H:i') }}</div>
            <div>
                <div class="timeline-main">
                    {{ $log->from_status ? (\App\Models\FormUsulan::STATUS_LABELS[$log->from_status] ?? $log->from_status) . ' → ' : 'Dikirim → ' }}<strong>{{ \App\Models\FormUsulan::STATUS_LABELS[$log->to_status] ?? $log->to_status }}</strong>
                </div>
                <div class="timeline-actor">oleh {{ $levelLabels[$log->actor?->role] ?? 'Sistem' }}</div>
                @if($log->catatan)
                <div class="timeline-note">{{ $log->catatan }}</div>
                @endif
            </div>
        </li>
        @endforeach
    </ul>
    @endif
</div>

{{-- ─── RINGKASAN USULAN ─── --}}
<div class="pb-detail-card animate-fade-in animate-delay-2">
    <div class="pb-detail-header">📄 Ringkasan Usulan</div>
    <div class="pb-detail-grid">
        <div class="pb-detail-row"><div class="pb-detail-label">Nama Lengkap</div><div class="pb-detail-value">{{ $usulan->nama_lengkap }}</div></div>
        <div class="pb-detail-row"><div class="pb-detail-label">NIP</div><div class="pb-detail-value">{{ $usulan->nip }}</div></div>
        <div class="pb-detail-row"><div class="pb-detail-label">Jabatan Fungsional</div><div class="pb-detail-value">{{ $usulan->jabatan_fungsional }}</div></div>
        <div class="pb-detail-row"><div class="pb-detail-label">Jenis Usulan</div><div class="pb-detail-value">{{ $usulan->jenis_usulan }}</div></div>
        <div class="pb-detail-row"><div class="pb-detail-label">Satuan Kerja</div><div class="pb-detail-value">{{ $usulan->satuan_kerja }}</div></div>
        <div class="pb-detail-row"><div class="pb-detail-label">Email</div><div class="pb-detail-value">{{ $usulan->email }}</div></div>
        <div class="pb-detail-row"><div class="pb-detail-label">Nomor Surat</div><div class="pb-detail-value">{{ $usulan->nomor_surat }}</div></div>
        <div class="pb-detail-row"><div class="pb-detail-label">Hal Surat</div><div class="pb-detail-value">{{ $usulan->hal_surat }}</div></div>
    </div>
</div>

@include('modules.partials.legacy-links', ['usulan' => $usulan])

<div class="pb-help animate-fade-in" style="margin-top:24px;">
    <span style="font-size:22px; flex-shrink:0;">💬</span>
    <div>
        <div style="font-weight:700; color:var(--c-heading); margin-bottom:6px; font-size:14px;">Ada pertanyaan?</div>
        <div style="font-size:13.5px; color:var(--c-text-2); line-height:1.7;">
            Jika ada pertanyaan terkait status usulan Anda, silakan hubungi Tim Kerja Pembinaan JF melalui halaman
            <a href="{{ route('tim-kerja') }}">Tim Kerja</a>.
        </div>
    </div>
</div>

@else

<div class="empty-state animate-fade-in">
    <div class="empty-state-icon">📭</div>
    @if($hasDraft)
    <h3>Usulan Belum Dikirim</h3>
    <p>Anda memiliki draf usulan yang belum dikirim. Lengkapi berkas persyaratan lalu klik "Kirim Usulan".</p>
    @else
    <h3>Belum Ada Usulan</h3>
    <p>Anda belum mengajukan usulan jabatan fungsional. Silakan lakukan Pengajuan Usulan terlebih dahulu.</p>
    @endif
    <a href="{{ route('pengajuan') }}" class="btn-to-form">📝 Pengajuan Usulan</a>
</div>

@endif
@endsection
