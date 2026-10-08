<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal Pembinaan Jabatan Fungsional BPS Republik Indonesia - Pusat">
    <title>Portal JF BPS RI - @yield('title', 'Beranda')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bps-dark: #003B73;
            --bps-mid: #0077B6;
            --bps-light: #90E0EF;
            --bps-orange: #F77F00;
            --bps-orange-dark: #D97706;
            --bg-slate: #F0F4F8;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            background: var(--bg-slate);
            color: #1e293b;
            min-height: 100vh;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: var(--bps-mid); border-radius: 3px; }

        /* Layout wrapper */
        .layout-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            width: 280px;
            min-width: 280px;
            background: linear-gradient(180deg, var(--bps-dark) 0%, #002855 60%, #001A3A 100%);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
            box-shadow: 4px 0 24px rgba(0,59,115,0.25);
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 28px 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-logo-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .sidebar-logo-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--bps-orange), #F59E0B);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 18px;
            color: white;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(247,127,0,0.4);
        }

        .sidebar-title {
            font-size: 13px;
            font-weight: 700;
            color: white;
            line-height: 1.3;
        }

        .sidebar-subtitle {
            font-size: 11px;
            color: rgba(255,255,255,0.5);
            font-weight: 400;
        }

        .sidebar-back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: rgba(255,255,255,0.08);
            border-radius: 8px;
            color: rgba(255,255,255,0.75);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-back-btn:hover {
            background: rgba(255,255,255,0.15);
            color: white;
            transform: translateX(-2px);
        }

        .sidebar-nav {
            padding: 20px 16px;
            flex: 1;
        }

        .sidebar-nav-label {
            font-size: 10px;
            font-weight: 700;
            color: rgba(255,255,255,0.4);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 0 8px;
            margin-bottom: 12px;
        }

        .sidebar-nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 10px;
            color: rgba(255,255,255,0.65);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 4px;
            transition: all 0.2s ease;
            position: relative;
        }

        .sidebar-nav-item:hover {
            background: rgba(255,255,255,0.08);
            color: white;
        }

        .sidebar-nav-item.active {
            background: linear-gradient(135deg, rgba(0,119,182,0.5), rgba(0,59,115,0.3));
            color: white;
            border: 1px solid rgba(144,224,239,0.2);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .sidebar-nav-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            background: var(--bps-orange);
            border-radius: 0 3px 3px 0;
        }

        .sidebar-nav-item .nav-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
            background: rgba(255,255,255,0.06);
        }

        .sidebar-nav-item.active .nav-icon {
            background: rgba(0,119,182,0.4);
        }

        .sidebar-footer {
            padding: 16px 24px 20px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        .sidebar-footer-text {
            font-size: 11px;
            color: rgba(255,255,255,0.3);
            line-height: 1.5;
        }

        /* MAIN CONTENT */
        .main-content {
            flex: 1;
            margin-left: 280px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* TOP HEADER BAR */
        .top-header {
            background: white;
            padding: 16px 32px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 1px 8px rgba(0,0,0,0.06);
        }

        .top-header-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #64748b;
        }

        .top-header-breadcrumb a {
            color: var(--bps-mid);
            text-decoration: none;
            font-weight: 500;
        }

        .top-header-breadcrumb a:hover { text-decoration: underline; }

        .top-header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .datetime-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 13px;
            color: #475569;
            font-weight: 500;
        }

        /* PAGE CONTENT */
        .page-content {
            padding: 32px;
            flex: 1;
        }

        /* PAGE HEADER */
        .page-header {
            margin-bottom: 32px;
        }

        .page-header-title {
            font-size: 28px;
            font-weight: 800;
            color: var(--bps-dark);
            line-height: 1.2;
            margin-bottom: 8px;
        }

        .page-header-desc {
            font-size: 15px;
            color: #64748b;
            font-weight: 400;
        }

        .page-header-divider {
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--bps-orange), var(--bps-mid));
            border-radius: 2px;
            margin-top: 16px;
        }

        /* CARDS */
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.04);
            border: 1px solid #f1f5f9;
            overflow: hidden;
        }

        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid #f1f5f9;
        }

        .card-body { padding: 24px; }

        /* BUTTONS */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--bps-dark), var(--bps-mid));
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(0,59,115,0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,59,115,0.4);
        }

        .btn-orange {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--bps-orange), #F59E0B);
            color: white;
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 16px rgba(247,127,0,0.4);
        }

        .btn-orange:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(247,127,0,0.5);
        }

        /* BADGE */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-blue { background: #dbeafe; color: #1d4ed8; }
        .badge-orange { background: #fef3c7; color: #d97706; }
        .badge-green { background: #dcfce7; color: #16a34a; }

        /* TABLE */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background: #f8fafc;
            padding: 12px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
        }

        .data-table td {
            padding: 14px 16px;
            font-size: 14px;
            color: #374151;
            border-bottom: 1px solid #f1f5f9;
        }

        .data-table tr:hover td { background: #f8fafc; }

        /* MOBILE SIDEBAR TOGGLE */
        .mobile-menu-btn {
            display: none;
            background: var(--bps-dark);
            border: none;
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
        }

        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .mobile-menu-btn { display: flex; align-items: center; }
            .page-content { padding: 20px; }
            .top-header { padding: 12px 20px; }
        }

        /* ANIMATIONS */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in {
            animation: fadeInUp 0.5s ease forwards;
        }

        .animate-delay-1 { animation-delay: 0.1s; opacity: 0; }
        .animate-delay-2 { animation-delay: 0.2s; opacity: 0; }
        .animate-delay-3 { animation-delay: 0.3s; opacity: 0; }
        .animate-delay-4 { animation-delay: 0.4s; opacity: 0; }
        .animate-delay-5 { animation-delay: 0.5s; opacity: 0; }

        /* OVERLAY */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 99;
            backdrop-filter: blur(2px);
        }

        .sidebar-overlay.active { display: block; }
    </style>
    @stack('styles')
</head>
<body>
    <div class="layout-wrapper">
        <!-- Overlay for mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo-wrapper">
                    <div class="sidebar-logo-icon">BPS</div>
                    <div>
                        <div class="sidebar-title">Portal Jabatan Fungsional</div>
                        <div class="sidebar-subtitle">BPS Republik Indonesia</div>
                    </div>
                </div>
                <a href="{{ route('home') }}" class="sidebar-back-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Beranda
                </a>
            </div>

            <nav class="sidebar-nav">
                <div class="sidebar-nav-label">Menu Utama</div>
                <a href="{{ route('alur-layanan') }}" class="sidebar-nav-item {{ request()->routeIs('alur-layanan') ? 'active' : '' }}">
                    <div class="nav-icon">🔄</div>
                    Alur Layanan
                </a>
                <a href="{{ route('form-usulan') }}" class="sidebar-nav-item {{ request()->routeIs('form-usulan') ? 'active' : '' }}">
                    <div class="nav-icon">📝</div>
                    Form Usulan
                </a>
                <a href="{{ route('uji-kompetensi') }}" class="sidebar-nav-item {{ request()->routeIs('uji-kompetensi') ? 'active' : '' }}">
                    <div class="nav-icon">🎓</div>
                    Uji Kompetensi
                </a>
                <a href="{{ route('regulasi') }}" class="sidebar-nav-item {{ request()->routeIs('regulasi') ? 'active' : '' }}">
                    <div class="nav-icon">📋</div>
                    Regulasi
                </a>
                <a href="{{ route('tim-kerja') }}" class="sidebar-nav-item {{ request()->routeIs('tim-kerja') ? 'active' : '' }}">
                    <div class="nav-icon">👥</div>
                    Tim Kerja
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-footer-text">
                    Tim Kerja Pembinaan<br>Jabatan Fungsional BPS RI<br>
                    <span style="color: rgba(255,255,255,0.5);">© 2026 BPS RI</span>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <!-- TOP HEADER -->
            <header class="top-header">
                <div style="display:flex; align-items: center; gap: 16px;">
                    <button class="mobile-menu-btn" onclick="toggleSidebar()" id="menuBtn">
                        ☰
                    </button>
                    <nav class="top-header-breadcrumb">
                        <a href="{{ route('home') }}">🏠 Beranda</a>
                        <span>›</span>
                        <span style="color: #1e293b; font-weight: 600;">@yield('breadcrumb', 'Halaman')</span>
                    </nav>
                </div>
                <div class="top-header-right" style="display:flex; align-items:center; gap:12px;">
                    <div class="datetime-badge" id="datetimeBadge">
                        <span>📅</span>
                        <span id="headerDatetime">--</span>
                    </div>
                    @auth
                    <div style="display:flex; align-items:center; gap:8px; background:#f8fafc; border:1px solid #e2e8f0; padding:4px 12px; border-radius:24px;">
                        <span style="font-size:12.5px; font-weight:700; color:#1e293b;">👤 {{ Auth::user()->name }}</span>
                        @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" style="font-size:11px; font-weight:700; color:#1d4ed8; text-decoration:none; background:#eff6ff; padding:2px 8px; border-radius:10px;">
                            🛡️ Admin
                        </a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST" style="display:inline; margin:0;">
                            @csrf
                            <button type="submit" style="background:none; border:none; color:#ef4444; font-size:12px; font-weight:700; cursor:pointer; padding:2px 4px;">
                                Logout 🚪
                            </button>
                        </form>
                    </div>
                    @endauth
                </div>
            </header>

            <!-- PAGE CONTENT -->
            <main class="page-content">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        }

        function updateDatetime() {
            const now = new Date();
            const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
            const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            const dayName = days[now.getDay()];
            const date = now.getDate();
            const month = months[now.getMonth()];
            const year = now.getFullYear();
            const hours = String(now.getHours()).padStart(2,'0');
            const minutes = String(now.getMinutes()).padStart(2,'0');
            document.getElementById('headerDatetime').textContent = `${dayName}, ${date} ${month} ${year} ${hours}:${minutes}`;
        }

        updateDatetime();
        setInterval(updateDatetime, 60000);
    </script>
    @stack('scripts')
</body>
</html>
