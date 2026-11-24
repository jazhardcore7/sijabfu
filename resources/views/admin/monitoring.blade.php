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
        .top-navbar {
            background: var(--c-surface);
            border-bottom: 1px solid var(--c-border);
            padding: 12px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
        }

        .navbar-logo-img {
            height: 38px;
            width: auto;
        }

        .navbar-titles {
            display: flex;
            flex-direction: column;
        }

        .navbar-title {
            font-size: 17px;
            font-weight: 800;
            color: var(--bps-dark);
            line-height: 1.1;
        }

        .navbar-subtitle {
            font-size: 11px;
            color: var(--bps-mid);
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-link-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 12.5px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
            color: var(--c-text-2);
            background: var(--c-surface-3);
        }

        .nav-link-btn:hover {
            background: var(--c-border);
            color: var(--bps-dark);
        }

        .nav-link-btn.active {
            background: linear-gradient(135deg, var(--bps-dark), var(--bps-mid));
            color: #FFFFFF;
        }

        .user-action-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--c-surface-2);
            border: 1px solid var(--c-border);
            padding: 5px 12px 5px 6px;
            border-radius: 50px;
        }

        .user-badge-role {
            background: #EF4444;
            color: #FFFFFF;
            font-size: 10px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 50px;
            letter-spacing: 0.5px;
        }

        .user-name-text {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--c-text);
        }

        .btn-logout {
            background: #FEE2E2;
            color: #991B1B;
            border: none;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-logout:hover {
            background: #FCA5A5;
        }

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
            color: var(--bps-dark);
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
            .top-navbar {
                padding: 12px 16px;
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

@section('content')
    <!-- 2. PAGE HEADER -->
    <div class="page-header-container">
        <div class="header-card">
            <div class="header-title-box">
                <h1>📊 Monitoring & Manajemen Progres Usulan Pegawai</h1>
                <p>
                    Pantau alur 5 tahapan usulan jabatan fungsional dari seluruh Satker BPS.
                    Perbarui status progres tahapan serta berikan catatan resmi langsung ke pegawai pengusul.
                </p>
            </div>
            <div class="header-badge-live">
                <span class="live-dot"></span>
                <span>Live Monitoring Active</span>
            </div>
        </div>
    </div>

    <!-- 3. STATS METRICS -->
    <section class="stats-grid">
        <a href="{{ route('admin.monitoring') }}" class="stat-card {{ !request('status') ? 'active-filter' : '' }}">
            <div class="stat-header">
                <span class="stat-label">Total Usulan</span>
                <span class="stat-icon">📁</span>
            </div>
            <div class="stat-count">{{ $stats['total'] }}</div>
            <div class="stat-sub">Seluruh Berkas</div>
        </a>

        <a href="{{ route('admin.monitoring', ['status' => 1]) }}" class="stat-card {{ request('status') == 1 ? 'active-filter' : '' }}">
            <div class="stat-header">
                <span class="stat-label">1. Pengusulan</span>
                <span class="stat-icon">📝</span>
            </div>
            <div class="stat-count" style="color: #EA580C;">{{ $stats['tahap_1'] }}</div>
            <div class="stat-sub">Satker / Pejabat JF</div>
        </a>

        <a href="{{ route('admin.monitoring', ['status' => 2]) }}" class="stat-card {{ request('status') == 2 ? 'active-filter' : '' }}">
            <div class="stat-header">
                <span class="stat-label">2. Verifikasi</span>
                <span class="stat-icon">🔍</span>
            </div>
            <div class="stat-count" style="color: #0284C7;">{{ $stats['tahap_2'] }}</div>
            <div class="stat-sub">Tim Pembinaan JF</div>
        </a>

        <a href="{{ route('admin.monitoring', ['status' => 3]) }}" class="stat-card {{ request('status') == 3 ? 'active-filter' : '' }}">
            <div class="stat-header">
                <span class="stat-label">3. Jadwal & Kuota</span>
                <span class="stat-icon">📅</span>
            </div>
            <div class="stat-count" style="color: #16A34A;">{{ $stats['tahap_3'] }}</div>
            <div class="stat-sub">Unit Kompetensi</div>
        </a>

        <a href="{{ route('admin.monitoring', ['status' => 4]) }}" class="stat-card {{ request('status') == 4 ? 'active-filter' : '' }}">
            <div class="stat-header">
                <span class="stat-label">4. Undangan</span>
                <span class="stat-icon">📨</span>
            </div>
            <div class="stat-count" style="color: #4F46E5;">{{ $stats['tahap_4'] }}</div>
            <div class="stat-sub">Sistem / SIGMA</div>
        </a>

        <a href="{{ route('admin.monitoring', ['status' => 5]) }}" class="stat-card {{ request('status') == 5 ? 'active-filter' : '' }}">
            <div class="stat-header">
                <span class="stat-label">5. Selesai</span>
                <span class="stat-icon">✅</span>
            </div>
            <div class="stat-count" style="color: #D97706;">{{ $stats['tahap_5'] }}</div>
            <div class="stat-sub">Konfirmasi Hadir</div>
        </a>
    </section>

    <!-- 4. MAIN MONITORING TABLE & CONTROLS -->
    <main class="main-container">

        @if(session('success'))
        <div class="alert-floating">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        <!-- FILTER & SEARCH BAR -->
        <div class="filter-panel">
            <form action="{{ route('admin.monitoring') }}" method="GET" class="filter-form">
                <div class="search-input-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" name="q" placeholder="Cari nama pegawai, NIP, Satker, atau Jabatan Fungsional..." value="{{ request('q') }}">
                </div>

                <select name="status" class="select-filter">
                    <option value="">-- Semua Tahapan Status --</option>
                    @foreach($tahapan as $t)
                    <option value="{{ $t['step'] }}" {{ request('status') == $t['step'] ? 'selected' : '' }}>
                        Tahap {{ $t['step'] }}: {{ $t['label'] }}
                    </option>
                    @endforeach
                </select>

                <button type="submit" class="btn-filter">Terapkan Filter</button>
                @if(request('q') || request('status'))
                <a href="{{ route('admin.monitoring') }}" class="btn-reset">Reset Filter</a>
                @endif
            </form>
        </div>

        <!-- LIST USULAN -->
        <div class="monitoring-list">
            @forelse($daftarUsulan as $index => $item)
            @php
                $stepClass = match($item->status) {
                    1 => 'badge-step-1',
                    2 => 'badge-step-2',
                    3 => 'badge-step-3',
                    4 => 'badge-step-4',
                    5 => 'badge-step-5',
                    default => 'badge-step-1'
                };
                $currTahap = collect($tahapan)->firstWhere('step', $item->status);
                $persen = $item->progressPersen();
            @endphp
            <div class="usulan-item-card">
                <!-- TOP BAR -->
                <div class="card-top-bar">
                    <div class="card-top-left">
                        <div class="pegawai-avatar">
                            {{ strtoupper(substr($item->nama_lengkap, 0, 2)) }}
                        </div>
                        <div>
                            <div class="pegawai-info-title">{{ $item->nama_lengkap }}</div>
                            <div class="pegawai-info-meta">
                                <span><strong>NIP:</strong> {{ $item->nip }}</span>
                                <span>•</span>
                                <span>🏢 {{ $item->satuan_kerja }}</span>
                                <span>•</span>
                                <span>✉️ {{ $item->email }}</span>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="status-badge-pill {{ $stepClass }}">
                            ● Tahap {{ $item->status }}: {{ $currTahap['label'] ?? $item->statusLabel() }} ({{ $persen }}%)
                        </span>
                        @if($safeBerkas = \App\Models\FormUsulan::safeUrl($item->link_berkas))
                        <a href="{{ $safeBerkas }}" target="_blank" rel="noopener noreferrer" class="link-drive-btn">
                            📁 Berkas Laci/Drive ↗
                        </a>
                        @endif
                    </div>
                </div>

                <!-- BODY: DOSSIER & PROGRESS TRACKER -->
                <div class="card-body-content">
                    <div>
                        <!-- DOSSIER DETAILS -->
                        <div class="dossier-grid">
                            <div class="dossier-cell">
                                <span class="dossier-label">Jabatan Fungsional</span>
                                <span class="dossier-value" style="color: var(--bps-navy);">{{ $item->jabatan_fungsional }}</span>
                            </div>
                            <div class="dossier-cell">
                                <span class="dossier-label">Jenis Usulan</span>
                                <span class="dossier-value">{{ $item->jenis_usulan }}</span>
                            </div>
                            <div class="dossier-cell">
                                <span class="dossier-label">Nomor Surat</span>
                                <span class="dossier-value">{{ $item->nomor_surat }}</span>
                            </div>
                            <div class="dossier-cell" style="grid-column: span 2;">
                                <span class="dossier-label">Hal Surat</span>
                                <span class="dossier-value">{{ $item->hal_surat }}</span>
                            </div>
                            <div class="dossier-cell">
                                <span class="dossier-label">Tanggal Usulan Masuk</span>
                                <span class="dossier-value">{{ $item->created_at->format('d M Y, H:i') }} WIB</span>
                            </div>
                        </div>

                        <!-- 5-STAGE MINI TRACKER (ATTACHMENT 1) -->
                        <div style="font-size: 11.5px; font-weight: 700; color: var(--c-text-2); margin-bottom: 4px;">
                            Alur 5 Tahapan Swimlane:
                        </div>
                        <div class="mini-tracker">
                            @foreach($tahapan as $t)
                            @php
                                $s = $t['step'];
                                $isDone = $item->status > $s;
                                $isActive = $item->status == $s;
                                $stateClass = $isDone ? 'done' : ($isActive ? 'active' : '');
                                $iconText = $isDone ? '✓' : $s;
                            @endphp
                            <div class="tracker-step-node">
                                <div class="tracker-circle {{ $stateClass }}">{{ $iconText }}</div>
                                <div class="tracker-step-text" style="{{ $isActive ? 'color: var(--bps-mid); font-weight:800;' : '' }}">
                                    {{ $t['label'] }}
                                </div>
                                <div class="tracker-actor-text">{{ $t['aktor'] }}</div>
                            </div>
                            @endforeach
                        </div>

                        <!-- CATATAN DARI ADMIN SAAT INI -->
                        @if($item->catatan)
                        <div style="background: #FFFBEB; border: 1px solid #FDE68A; padding: 10px 14px; border-radius: 10px; font-size: 12px; color: #92400E; margin-top: 12px;">
                            <strong>Catatan untuk Pegawai saat ini:</strong> {{ $item->catatan }}
                        </div>
                        @endif
                    </div>

                    <!-- RIGHT CONTROLS: UPDATE STATUS & CATATAN -->
                    <div class="control-panel-box">
                        <div class="control-panel-title">
                            <span>⚙️</span>
                            <span>Kelola Progres Pegawai</span>
                        </div>

                        <form action="{{ route('admin.usulan.update-status', $item->id) }}" method="POST" style="display: flex; flex-direction: column; gap: 12px;">
                            @csrf
                            <div class="form-control-group">
                                <label class="form-control-label">Status Progres Tahapan:</label>
                                <select name="status" class="select-status-lg" required>
                                    @foreach($tahapan as $t)
                                    <option value="{{ $t['step'] }}" {{ $item->status == $t['step'] ? 'selected' : '' }}>
                                        Tahap {{ $t['step'] }}: {{ $t['label'] }} ({{ $t['aktor'] }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-control-group">
                                <label class="form-control-label">Catatan Resmi untuk Pegawai:</label>
                                <textarea name="catatan" class="textarea-catatan" placeholder="Tulis instruksi, jadwal, atau perbaikan berkas untuk pegawai...">{{ $item->catatan }}</textarea>
                            </div>

                            <button type="submit" class="btn-update-submit">
                                <span>💾</span>
                                <span>Simpan Perubahan</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div style="background: var(--c-surface); border-radius: 18px; padding: 60px 20px; text-align: center; border: 2px dashed var(--c-border-strong);">
                <div style="font-size: 48px; margin-bottom: 12px;">📭</div>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--c-text); margin-bottom: 6px;">Tidak ada usulan ditemukan</h3>
                <p style="font-size: 13px; color: var(--c-text-muted);">Silakan sesuaikan filter pencarian atau tunggu pengajuan usulan baru dari pegawai.</p>
            </div>
            @endforelse
        </div>

        <!-- PAGINATION -->
        <div style="margin-top: 24px;">
            {{ $daftarUsulan->links() }}
        </div>

    </main>

    <!-- 5. FOOTER -->
    <footer class="footer-bar">
        Copyright &copy; 2026 Badan Pusat Statistik RI — Tim Pembinaan JF (SIGMA JF)
    </footer>
@endsection
