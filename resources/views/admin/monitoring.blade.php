@extends('layouts.app')

@section('title', 'Monitoring Progress UKOM')
@section('breadcrumb', 'Monitoring Progress UKOM')
@section('flush', '1')

@push('styles')
<style>
        :root {
            --bps-dark: #002855;
            --bps-navy: #003B73;
            --bps-mid: #005BAA;
            --bps-light: #0284C7;
            --bg-page: var(--c-bg);
            --text-main: var(--c-text);
            --text-muted: var(--c-text-muted);
        }

        /* 1. TOP NAVBAR */

        /* 2. PAGE HEADER */
        .page-header-container {
            max-width: 1400px;
            margin: 28px auto 0 auto;
            padding: 0 32px;
            width: 100%;
        }

        .header-card {
            background: linear-gradient(135deg, #002855 0%, #003B73 50%, #005BAA 100%);
            border-radius: 20px;
            padding: 28px 32px;
            color: white;
            box-shadow: 0 4px 20px rgba(0, 40, 85, 0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .header-title-box h1 {
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .header-title-box p {
            font-size: 13.5px;
            color: rgba(255, 255, 255, 0.85);
            max-width: 650px;
            line-height: 1.5;
        }

        .header-badge-live {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid #10B981;
            color: #A7F3D0;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background: #10B981;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 8px #10B981;
        }

        /* 3. METRIC STATS CARDS */
        .stats-grid {
            max-width: 1400px;
            margin: 20px auto 0 auto;
            padding: 0 32px;
            width: 100%;
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 14px;
        }

        .stat-card {
            background: var(--c-surface);
            border-radius: 16px;
            padding: 16px 18px;
            border: 1px solid var(--c-border);
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
            gap: 4px;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            color: inherit;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.06);
        }

        .stat-card.active-filter {
            border-color: var(--bps-mid);
            background: #F0F9FF;
            box-shadow: 0 0 0 2px rgba(0, 91, 170, 0.2);
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-icon {
            font-size: 16px;
        }

        .stat-count {
            font-size: 24px;
            font-weight: 900;
            color: var(--c-text);
            line-height: 1.1;
            margin-top: 4px;
        }

        .stat-label {
            font-size: 11.5px;
            font-weight: 700;
            color: var(--c-text-muted);
            line-height: 1.3;
        }

        .stat-sub {
            font-size: 10px;
            color: var(--c-text-faint);
        }

        /* 4. MAIN CONTENT & FILTER */
        .main-container {
            max-width: 1400px;
            margin: 24px auto 40px auto;
            padding: 0 32px;
            width: 100%;
            flex: 1;
        }

        .filter-panel {
            background: var(--c-surface);
            border-radius: 16px;
            padding: 16px 20px;
            border: 1px solid var(--c-border);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
        }

        .filter-form {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            flex: 1;
        }

        .search-input-box {
            position: relative;
            flex: 1;
            min-width: 240px;
        }

        .search-input-box input {
            width: 100%;
            padding: 10px 14px 10px 36px;
            border-radius: 10px;
            border: 1.5px solid var(--c-border-strong);
            font-size: 13px;
            outline: none;
        }

        .search-input-box input:focus {
            border-color: var(--bps-mid);
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--c-text-faint);
            font-size: 14px;
        }

        .select-filter {
            padding: 10px 14px;
            border-radius: 10px;
            border: 1.5px solid var(--c-border-strong);
            font-size: 13px;
            font-weight: 600;
            color: var(--c-text);
            outline: none;
            background: var(--c-surface);
            cursor: pointer;
        }

        .btn-filter {
            background: var(--bps-navy);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-filter:hover {
            background: var(--bps-dark);
        }

        .btn-reset {
            color: var(--c-text-muted);
            text-decoration: none;
            font-size: 12.5px;
            font-weight: 600;
            padding: 10px 14px;
        }

        .btn-reset:hover {
            color: var(--c-text);
        }

        /* 5. USULAN MONITORING CARDS / ROWS */
        .monitoring-list {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .usulan-item-card {
            background: var(--c-surface);
            border-radius: 18px;
            border: 1px solid var(--c-border);
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            overflow: hidden;
            transition: all 0.2s;
        }

        .usulan-item-card:hover {
            box-shadow: 0 6px 18px rgba(0,0,0,0.06);
            border-color: var(--c-border-strong);
        }

        .card-top-bar {
            padding: 16px 24px;
            background: var(--c-surface-2);
            border-bottom: 1px solid var(--c-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .card-top-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .pegawai-avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--bps-mid), var(--bps-light));
            color: white;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .pegawai-info-title {
            font-size: 15px;
            font-weight: 800;
            color: var(--c-text);
        }

        .pegawai-info-meta {
            font-size: 12px;
            color: var(--c-text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .status-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 800;
        }

        .badge-step-1 { background: #FFEDD5; color: #9A3412; border: 1px solid #FED7AA; }
        .badge-step-2 { background: #DBEAFE; color: #1E40AF; border: 1px solid #BFDBFE; }
        .badge-step-3 { background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0; }
        .badge-step-4 { background: #E0E7FF; color: #3730A3; border: 1px solid #C7D2FE; }
        .badge-step-5 { background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; }

        .card-body-content {
            padding: 24px;
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 24px;
        }

        /* LEFT SIDE: DOSSIER & PROGRESS */
        .dossier-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 20px;
            background: var(--c-surface-2);
            padding: 16px;
            border-radius: 12px;
            border: 1px solid var(--c-border);
        }

        .dossier-cell {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .dossier-label {
            font-size: 10.5px;
            font-weight: 700;
            color: var(--c-text-faint);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .dossier-value {
            font-size: 13px;
            font-weight: 700;
            color: var(--c-text);
            word-break: break-word;
        }

        /* SWIMLANE MINI TRACKER */
        .mini-tracker {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            margin: 24px 0 16px 0;
            padding: 0 10px;
        }

        .mini-tracker::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 20px;
            right: 20px;
            height: 4px;
            background: var(--c-border);
            z-index: 1;
        }

        .tracker-step-node {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            width: 75px;
            text-align: center;
        }

        .tracker-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--c-surface);
            border: 2px solid var(--c-border-strong);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 800;
            color: var(--c-text-muted);
            margin-bottom: 6px;
            transition: all 0.2s;
        }

        .tracker-circle.done {
            background: #10B981;
            border-color: #10B981;
            color: white;
        }

        .tracker-circle.active {
            background: var(--bps-mid);
            border-color: var(--bps-mid);
            color: white;
            box-shadow: 0 0 0 4px rgba(0, 91, 170, 0.2);
        }

        .tracker-step-text {
            font-size: 10.5px;
            font-weight: 700;
            color: var(--c-text-2);
            line-height: 1.2;
        }

        .tracker-actor-text {
            font-size: 9.5px;
            color: var(--c-text-faint);
            margin-top: 2px;
        }

        /* RIGHT SIDE: STATUS UPDATE CONTROL PANEL */
        .control-panel-box {
            background: var(--c-surface-2);
            border: 1.5px solid var(--c-border);
            border-radius: 14px;
            padding: 18px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .control-panel-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--c-heading);
            display: flex;
            align-items: center;
            gap: 6px;
            border-bottom: 1px solid var(--c-border);
            padding-bottom: 8px;
        }

        .form-control-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .form-control-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--c-text-2);
        }

        .select-status-lg {
            width: 100%;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1.5px solid var(--c-border-strong);
            font-size: 12px;
            font-weight: 700;
            color: var(--c-text);
            background: var(--c-surface);
            cursor: pointer;
        }

        .textarea-catatan {
            width: 100%;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1.5px solid var(--c-border-strong);
            font-size: 12px;
            font-family: inherit;
            resize: vertical;
            min-height: 60px;
        }

        .btn-update-submit {
            background: linear-gradient(135deg, var(--bps-dark), var(--bps-mid));
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-update-submit:hover {
            box-shadow: 0 4px 12px rgba(0, 59, 115, 0.25);
            transform: translateY(-1px);
        }

        .link-drive-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #0284C7;
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 11.5px;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.2s;
        }

        .link-drive-btn:hover {
            background: #0369A1;
        }

        /* ALERT NOTIFICATION */
        .alert-floating {
            background: #D1FAE5;
            border: 1px solid #6EE7B7;
            color: #065F46;
            padding: 14px 20px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 13.5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* FOOTER */
        .footer-bar {
            background: #001F42;
            color: rgba(255,255,255,0.6);
            padding: 18px 32px;
            font-size: 12px;
            text-align: center;
            margin-top: auto;
        }

        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            .card-body-content {
                grid-template-columns: 1fr;
            }
            .dossier-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 640px) {
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            .page-header-container, .main-container {
                padding: 0 16px;
            }
            .dossier-grid {
                grid-template-columns: 1fr;
            }
            .mini-tracker {
                display: none;
            }
        }
</style>
@endpush

@push('styles')
<style>
    .alert-error-box {
        background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B;
        padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 600;
        margin-bottom: 16px; display: flex; gap: 8px; align-items: center;
    }
    .mon-action-form { display: flex; flex-direction: column; gap: 8px; padding-top: 10px; border-top: 1px dashed var(--c-border); margin-top: 10px; }
    .mon-action-form:first-of-type { border-top: none; padding-top: 0; margin-top: 0; }
    .mon-file-input { font-size: 12px; color: var(--c-text-2); }
    .mon-input {
        border: 1.5px solid var(--c-border-strong); border-radius: 8px; padding: 8px 10px;
        font-size: 12.5px; color: var(--c-text); background: var(--c-surface); font-family: inherit; width: 100%;
    }
    .mon-hint { font-size: 11.5px; color: var(--c-text-muted); line-height: 1.5; }
    .mon-btn-link {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        padding: 10px; border-radius: 10px; text-decoration: none;
        font-size: 13px; font-weight: 700; color: #FFFFFF;
        background: linear-gradient(135deg, var(--bps-dark), var(--bps-mid));
    }
    .mon-doc-links { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px; font-size: 12px; }
    .mon-doc-links a { color: var(--bps-mid); font-weight: 700; text-decoration: none; }
    .btn-rollback { background: #B45309 !important; }
</style>
@endpush

@section('content')
    <!-- PAGE HEADER -->
    <div class="page-header-container">
        <div class="header-card">
            <div class="header-title-box">
                <h1>📊 Monitoring Progress UKOM</h1>
                <p>
                    Pantau progres setiap pegawai, terbitkan surat usulan, dan unggah sertifikat hasil UKOM.
                    Perubahan status mengikuti alur Verifikasi → Disetujui → Ditetapkan → Selesai.
                </p>
            </div>
        </div>
    </div>

    <!-- STATISTIK PER STATUS (FR-MON-1) -->
    <section class="stats-grid">
        <a href="{{ route('monitoring.index', array_filter(['q' => request('q')])) }}" class="stat-card {{ !request('status') ? 'active-filter' : '' }}" data-stat="total">
            <div class="stat-header">
                <span class="stat-label">Total Usulan</span>
                <span class="stat-icon">📁</span>
            </div>
            <div class="stat-count">{{ $stats['total'] }}</div>
            <div class="stat-sub">Seluruh usulan terkirim</div>
        </a>
        @php
            $statMeta = [
                'verifikasi' => ['🔍', '#0284C7'],
                'perbaikan' => ['🛠️', '#EA580C'],
                'disetujui' => ['✅', '#16A34A'],
                'ditetapkan' => ['📄', '#4F46E5'],
                'selesai' => ['🎓', '#D97706'],
            ];
        @endphp
        @foreach($statusLabels as $key => $label)
        <a href="{{ route('monitoring.index', array_filter(['status' => $key, 'q' => request('q')])) }}" class="stat-card {{ request('status') === $key ? 'active-filter' : '' }}" data-stat="{{ $key }}">
            <div class="stat-header">
                <span class="stat-label">{{ $label }}</span>
                <span class="stat-icon">{{ $statMeta[$key][0] }}</span>
            </div>
            <div class="stat-count" style="color: {{ $statMeta[$key][1] }};" data-count="{{ $stats[$key] }}">{{ $stats[$key] }}</div>
            <div class="stat-sub">Usulan</div>
        </a>
        @endforeach
    </section>

    <main class="main-container">

        @if(session('success'))
        <div class="alert-floating">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if($transitionError ?? session('error'))
        <div class="alert-error-box" data-section="transition-error">
            <span>⚠️</span>
            <span>{{ $transitionError ?? session('error') }}</span>
        </div>
        @endif

        @if($errors->any())
        <div class="alert-error-box" data-section="validation-errors">
            <span>⚠️</span>
            <span>{{ implode(' ', $errors->all()) }}</span>
        </div>
        @endif

        <!-- FILTER & PENCARIAN -->
        <div class="filter-panel">
            <form action="{{ route('monitoring.index') }}" method="GET" class="filter-form">
                <div class="search-input-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" name="q" placeholder="Cari NIP atau nama pegawai..." value="{{ request('q') }}">
                </div>
                <select name="status" class="select-filter">
                    <option value="">-- Semua Status --</option>
                    @foreach($statusLabels as $key => $label)
                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-filter">Terapkan Filter</button>
                @if(request('q') || request('status'))
                <a href="{{ route('monitoring.index') }}" class="btn-reset">Reset Filter</a>
                @endif
            </form>
        </div>

        <!-- DAFTAR PROGRES PEGAWAI -->
        <div class="monitoring-list">
            @php $order = ['verifikasi' => 1, 'perbaikan' => 1, 'disetujui' => 2, 'ditetapkan' => 3, 'selesai' => 4]; @endphp
            @forelse($daftarUsulan as $item)
            @php
                $rank = $order[$item->status_key] ?? 1;
                $tahap = ['Verifikasi', 'Disetujui', 'Ditetapkan', 'Selesai'];
                $persen = $item->status_key === 'selesai' ? 100 : (int) round((($rank - 1) / 4) * 100);
                $formQuery = array_filter(['status' => request('status'), 'q' => request('q')]);
            @endphp
            <div class="usulan-item-card" data-usulan="{{ $item->id }}">
                <div class="card-top-bar">
                    <div class="card-top-left">
                        <div class="pegawai-avatar">{{ strtoupper(mb_substr($item->nama_lengkap, 0, 2)) }}</div>
                        <div>
                            <div class="pegawai-info-title">{{ $item->nama_lengkap }}</div>
                            <div class="pegawai-info-meta">
                                <span><strong>NIP:</strong> {{ $item->nip }}</span>
                                <span>•</span>
                                <span>🏢 {{ $item->satuan_kerja }}</span>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="status-badge-pill {{ $item->statusBadgeClass() }}" data-status-key="{{ $item->status_key }}">
                            ● {{ $item->statusKeyLabel() }} ({{ $persen }}%)
                        </span>
                        @if($safeBerkas = \App\Models\FormUsulan::safeUrl($item->link_berkas))
                        <a href="{{ $safeBerkas }}" target="_blank" rel="noopener noreferrer" class="link-drive-btn">📁 Berkas Laci/Drive ↗</a>
                        @endif
                    </div>
                </div>

                <div class="card-body-content">
                    <div>
                        <div class="dossier-grid">
                            <div class="dossier-cell">
                                <span class="dossier-label">Jabatan Fungsional</span>
                                <span class="dossier-value" style="color: var(--c-heading);">{{ $item->jabatan_fungsional }}</span>
                            </div>
                            <div class="dossier-cell">
                                <span class="dossier-label">Jenis Usulan</span>
                                <span class="dossier-value">{{ $item->jenis_usulan }}</span>
                            </div>
                            <div class="dossier-cell">
                                <span class="dossier-label">Hasil Verifikasi</span>
                                <span class="dossier-value">{{ $item->hasil_verifikasi ? strtoupper($item->hasil_verifikasi) : '-' }}</span>
                            </div>
                            <div class="dossier-cell" style="grid-column: span 2;">
                                <span class="dossier-label">Nomor / Hal Surat</span>
                                <span class="dossier-value">{{ $item->nomor_surat }} — {{ $item->hal_surat }}</span>
                            </div>
                            <div class="dossier-cell">
                                <span class="dossier-label">Dikirim</span>
                                <span class="dossier-value">{{ ($item->submitted_at ?? $item->created_at)->format('d M Y, H:i') }}</span>
                            </div>
                        </div>

                        <div class="mini-tracker">
                            @foreach($tahap as $i => $label)
                            @php
                                $s = $i + 1;
                                $isDone = $item->status_key === 'selesai' || $rank > $s;
                                $isActive = !$isDone && $rank === $s;
                                $stateClass = $isDone ? 'done' : ($isActive ? 'active' : '');
                            @endphp
                            <div class="tracker-step-node">
                                <div class="tracker-circle {{ $stateClass }}">{{ $isDone ? '✓' : $s }}</div>
                                <div class="tracker-step-text">{{ $label }}{{ $isActive && $item->status_key === 'perbaikan' ? ' (Perbaikan)' : '' }}</div>
                            </div>
                            @endforeach
                        </div>

                        @if($item->catatan)
                        <div style="background: #FFFBEB; border: 1px solid #FDE68A; padding: 10px 14px; border-radius: 10px; font-size: 12px; color: #92400E; margin-top: 12px;">
                            <strong>Catatan terbaru:</strong> {{ $item->catatan }}
                        </div>
                        @endif

                        <div class="mon-doc-links">
                            @if($item->surat_usulan_path)
                            <a href="{{ route('files.show', ['type' => 'surat', 'id' => $item->id, 'mode' => 'preview']) }}" target="_blank" rel="noopener">📄 Surat Usulan</a>
                            @endif
                            @if($item->sertifikat_path)
                            <a href="{{ route('files.show', ['type' => 'sertifikat', 'id' => $item->id, 'mode' => 'preview']) }}" target="_blank" rel="noopener">🎓 Sertifikat UKOM</a>
                            @endif
                            @if($item->hasil_ukom)
                            <span>Hasil UKOM: <strong>{{ $item->hasil_ukom }}</strong></span>
                            @endif
                        </div>
                    </div>

                    <!-- PANEL UPDATE STATUS (FR-MON-2, FR-MON-4, FR-MON-5) -->
                    <div class="control-panel-box">
                        <div class="control-panel-title">
                            <span>⚙️</span>
                            <span>Update Status</span>
                        </div>

                        @if(in_array($item->status_key, ['verifikasi', 'perbaikan'], true))
                        <a href="{{ route('verifikasi.show', $item->id) }}" class="mon-btn-link" data-action="ke-verifikasi">🔍 Verifikasi Berkas</a>
                        <div class="mon-hint">
                            {{ $item->status_key === 'perbaikan' ? 'Menunggu pegawai memperbaiki dan mengirim ulang berkas.' : 'Keputusan MS/TMS dilakukan di halaman Verifikasi Berkas.' }}
                        </div>
                        @elseif($item->status_key === 'disetujui')
                        <form action="{{ route('monitoring.update-status', ['usulan' => $item->id] + $formQuery) }}" method="POST" enctype="multipart/form-data" class="mon-action-form" data-form="tetapkan">
                            @csrf
                            <input type="hidden" name="aksi" value="tetapkan">
                            <label class="form-control-label">Upload Surat Usulan (PDF):</label>
                            <input type="file" name="surat" accept="application/pdf,.pdf" class="mon-file-input" required>
                            <input type="text" name="catatan" class="mon-input" placeholder="Catatan untuk pegawai (opsional)">
                            <button type="submit" class="btn-update-submit"><span>📄</span><span>Tetapkan</span></button>
                        </form>
                        @elseif($item->status_key === 'ditetapkan')
                        <form action="{{ route('monitoring.update-status', ['usulan' => $item->id] + $formQuery) }}" method="POST" enctype="multipart/form-data" class="mon-action-form" data-form="selesaikan">
                            @csrf
                            <input type="hidden" name="aksi" value="selesaikan">
                            <label class="form-control-label">Upload Sertifikat UKOM (PDF):</label>
                            <input type="file" name="sertifikat" accept="application/pdf,.pdf" class="mon-file-input" required>
                            <input type="text" name="hasil_ukom" class="mon-input" placeholder="Hasil UKOM (opsional)">
                            <input type="text" name="catatan" class="mon-input" placeholder="Catatan untuk pegawai (opsional)">
                            <button type="submit" class="btn-update-submit"><span>🎓</span><span>Selesai</span></button>
                        </form>
                        @else
                        <div class="mon-hint">Usulan telah selesai.</div>
                        @endif

                        @if($isSuperAdmin && ($order[$item->status_key] ?? 1) > 1)
                        <form action="{{ route('monitoring.update-status', ['usulan' => $item->id] + $formQuery) }}" method="POST" class="mon-action-form" data-form="rollback">
                            @csrf
                            <input type="hidden" name="aksi" value="rollback">
                            <label class="form-control-label">Rollback status (Super Admin):</label>
                            <select name="ke_status" class="mon-input" required>
                                @foreach($statusLabels as $key => $label)
                                    @if(($order[$key] ?? 1) < ($order[$item->status_key] ?? 1))
                                    <option value="{{ $key }}">{{ $label }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <input type="text" name="alasan" class="mon-input" placeholder="Alasan rollback (wajib)" required>
                            <button type="submit" class="btn-update-submit btn-rollback"><span>↩️</span><span>Rollback</span></button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div style="background: var(--c-surface); border-radius: 18px; padding: 60px 20px; text-align: center; border: 2px dashed var(--c-border-strong);">
                <div style="font-size: 48px; margin-bottom: 12px;">📭</div>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--c-text); margin-bottom: 6px;">Tidak ada usulan ditemukan</h3>
                <p style="font-size: 13px; color: var(--c-text-muted);">Sesuaikan filter pencarian atau tunggu usulan baru dari pegawai.</p>
            </div>
            @endforelse
        </div>

        <div style="margin-top: 24px;">
            {{ $daftarUsulan->links() }}
        </div>
    </main>

    <footer class="footer-bar">
        Copyright &copy; 2026 Badan Pusat Statistik RI — Tim Pembinaan JF (SIGMA JF)
    </footer>
@endsection
