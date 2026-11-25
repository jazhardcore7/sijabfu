<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SIGMA JF BPS Republik Indonesia - Pusat">
    <title>Portal JF BPS RI - @yield('title', 'Beranda')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @include('partials.theme')
    @stack('styles')
</head>
<body>
    @php
        $authUser = Auth::user();
        $dashboardRoute = $authUser->dashboardRoute();
    @endphp
    <div class="layout-wrapper">
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        {{-- Sidebar: menu per role (FR-DASH-2) --}}
        <aside class="sidebar" id="sidebar" aria-label="Menu utama">
            <div class="sidebar-header">
                <div class="sidebar-logo-wrapper">
                    <div class="sidebar-logo-icon" aria-hidden="true">BPS</div>
                    <div>
                        <div class="sidebar-title">SIGMA JF</div>
                        <div class="sidebar-subtitle">BPS Republik Indonesia</div>
                    </div>
                </div>
                <a href="{{ route($dashboardRoute) }}" class="sidebar-back-btn">
                    <x-icon name="arrow-left" />
                    Kembali ke Beranda
                </a>
            </div>

            @include('partials.sidebar')

            <div class="sidebar-footer">
                <div class="sidebar-footer-text">
                    Tim Pembinaan JF BPS RI<br>
                    © 2026 BPS RI
                </div>
            </div>
        </aside>

        <div class="main-content">
            {{-- Navbar (FR-DASH-1) --}}
            <header class="top-header" data-layout="navbar">
                <div class="top-header-left">
                    <button type="button" class="mobile-menu-btn" onclick="toggleSidebar()" id="menuBtn"
                            aria-controls="sidebar" aria-expanded="false" aria-label="Buka menu">
                        <x-icon name="menu" />
                    </button>
                    <nav class="top-header-breadcrumb" aria-label="Breadcrumb">
                        <a href="{{ route($dashboardRoute) }}" class="breadcrumb-home"><x-icon name="home" /> Beranda</a>
                        <span class="breadcrumb-sep" aria-hidden="true"><x-icon name="chevron-right" /></span>
                        <span class="breadcrumb-current" aria-current="page">@yield('breadcrumb', 'Halaman')</span>
                    </nav>
                </div>
                <div class="top-header-right navbar-actions">
                    <div class="datetime-badge" id="datetimeBadge">
                        <x-icon name="calendar" />
                        <span id="headerDatetime">--</span>
                    </div>

                    @include('partials.notifications')

                    <button type="button" class="theme-toggle" onclick="toggleTheme()" data-layout="theme-toggle" title="Ganti tema" aria-label="Ganti tema dark/light">
                        <span class="icon-light"><x-icon name="moon" /></span>
                        <span class="icon-dark"><x-icon name="sun" /></span>
                    </button>

                    {{-- Profil dengan tingkatan user --}}
                    <details class="nav-dropdown" data-layout="profile-menu">
                        <summary class="profile-btn" aria-label="Profil {{ $authUser->name }}">
                            <x-icon name="user" /> <span class="profile-name">{{ $authUser->name }}</span>
                            <span class="level-badge" data-level="{{ $authUser->role }}">{{ $authUser->levelLabel() }}</span>
                        </summary>
                        <div class="nav-dropdown-panel">
                            <div class="profile-panel-name">{{ $authUser->name }}</div>
                            <div class="profile-panel-meta">
                                NIP {{ $authUser->nip ?? '-' }}<br>
                                Tingkatan: {{ $authUser->levelLabel() }}
                            </div>
                            <form action="{{ route('logout') }}" method="POST" data-layout="logout">
                                @csrf
                                <button type="submit" class="btn-logout-nav"><x-icon name="log-out" /> Logout</button>
                            </form>
                        </div>
                    </details>
                </div>
            </header>

            <main class="page-content @hasSection('flush') page-content--flush @endif" id="main">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const open = sidebar.classList.toggle('open');
            overlay.classList.toggle('active', open);
            document.getElementById('menuBtn').setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && document.getElementById('sidebar').classList.contains('open')) {
                toggleSidebar();
            }
        });

        function updateDatetime() {
            const now = new Date();
            const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
            const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            const hours = String(now.getHours()).padStart(2,'0');
            const minutes = String(now.getMinutes()).padStart(2,'0');
            document.getElementById('headerDatetime').textContent =
                `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()} ${hours}:${minutes}`;
        }

        updateDatetime();
        setInterval(updateDatetime, 60000);
    </script>
    @stack('scripts')
</body>
</html>
