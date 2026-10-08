<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Pembinaan Jabatan Fungsional - BPS RI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bps-dark: #003B73;
            --bps-mid: #0077B6;
            --bps-cyan: #00A3E0;
            --bps-orange: #F77F00;
            --bps-green: #15803D;
            --card-blue-bg: #E0F2FE;
            --card-blue-border: #BAE6FD;
            --card-green-bg: #DCFCE7;
            --card-green-border: #BBF7D0;
            --card-orange-bg: #FFEDD5;
            --card-orange-border: #FED7AA;
            --dark-bar: #102A43;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: #FFFFFF;
            color: #1E293B;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* 1. TOP NAVBAR */
        .top-navbar {
            background: #FFFFFF;
            padding: 14px 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #F1F5F9;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .navbar-logo-img {
            height: 44px;
            width: auto;
            object-fit: contain;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .navbar-titles {
            text-align: right;
        }

        .navbar-title {
            font-size: 17px;
            font-weight: 800;
            color: var(--bps-dark);
            line-height: 1.2;
        }

        .navbar-subtitle {
            font-size: 13px;
            color: #64748B;
            font-weight: 500;
        }

        .user-action-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            padding: 6px 14px;
            border-radius: 30px;
        }

        .user-badge-role {
            background: #EFF6FF;
            color: #1D4ED8;
            font-size: 11px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 12px;
            border: 1px solid #BFDBFE;
        }

        .user-name-text {
            font-size: 13px;
            font-weight: 700;
            color: #1E293B;
        }

        .btn-portal-link {
            font-size: 12px;
            font-weight: 600;
            color: var(--bps-mid);
            text-decoration: none;
            padding: 4px 10px;
            border-radius: 8px;
            background: #F0F9FF;
            transition: all 0.2s;
        }

        .btn-portal-link:hover {
            background: #E0F2FE;
            color: var(--bps-dark);
        }

        .btn-logout {
            background: none;
            border: none;
            color: #EF4444;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 6px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .btn-logout:hover {
            background: #FEE2E2;
        }

        /* 2. HERO WELCOME BANNER (ATTACHMENT 1) */
        .hero-banner {
            position: relative;
            background: linear-gradient(135deg, #E0F2FE 0%, #EFF6FF 55%, #DBEAFE 100%);
            padding: 32px 48px;
            display: grid;
            grid-template-columns: 1.25fr 0.75fr;
            align-items: center;
            border-bottom: 1px solid #E2E8F0;
            overflow: hidden;
        }

        .hero-banner::before {
            content: '';
            position: absolute;
            top: -50px;
            left: -40px;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, 0.45);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-content {
            z-index: 2;
            max-width: 650px;
        }

        .welcome-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #BAE6FD;
            color: #0369A1;
            padding: 6px 18px;
            border-radius: 30px;
            font-size: 13.5px;
            font-weight: 700;
            margin-bottom: 14px;
            box-shadow: 0 2px 8px rgba(3, 105, 161, 0.12);
        }

        .hero-heading {
            font-size: 34px;
            font-weight: 900;
            color: var(--bps-dark);
            line-height: 1.2;
            margin-bottom: 10px;
        }

        .hero-heading span {
            color: var(--bps-mid);
        }

        .hero-description {
            font-size: 15px;
            color: #475569;
            font-weight: 500;
            line-height: 1.5;
        }

        .hero-illustration {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: flex-end;
            align-items: flex-end;
        }

        .staff-img {
            max-height: 195px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 8px 18px rgba(0, 59, 115, 0.18));
        }

        /* 3. MAIN CARDS CONTAINER */
        .cards-container {
            flex: 1;
            max-width: 1360px;
            width: 100%;
            margin: 0 auto;
            padding: 40px 32px 50px 32px;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
        }

        /* CARD GENERAL */
        .link-card {
            border-radius: 26px;
            padding: 24px 22px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            border-width: 1.5px;
            border-style: solid;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .link-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.08);
        }

        /* COLOR THEMES FOR 3 CARDS */
        .card-blue {
            background: var(--card-blue-bg);
            border-color: var(--card-blue-border);
        }

        .card-green {
            background: var(--card-green-bg);
            border-color: var(--card-green-border);
        }

        .card-orange {
            background: var(--card-orange-bg);
            border-color: var(--card-orange-border);
        }

        /* CARD HEADER */
        .card-header-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 6px;
        }

        .card-header-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
        }

        .icon-blue-bg {
            background: linear-gradient(135deg, #0284C7 0%, #0369A1 100%);
        }

        .icon-green-bg {
            background: linear-gradient(135deg, #16A34A 0%, #15803D 100%);
        }

        .icon-orange-bg {
            background: linear-gradient(135deg, #F97316 0%, #EA580C 100%);
        }

        .card-header-title {
            font-size: 19px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: -0.2px;
        }

        /* LINK ITEMS INSIDE CARD */
        .link-items-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .link-item-row {
            background: #FFFFFF;
            border-radius: 14px;
            padding: 13px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-decoration: none;
            color: #1E293B;
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
            transition: all 0.2s ease;
        }

        .link-item-row:hover {
            transform: translateX(4px);
            background: #FFFFFF;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
            border-color: #CBD5E1;
        }

        .link-item-left {
            display: flex;
            align-items: center;
            gap: 12px;
            overflow: hidden;
        }

        .link-item-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
            background: #F1F5F9;
        }

        .link-item-text {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .link-item-arrow {
            color: #94A3B8;
            font-size: 16px;
            font-weight: 700;
            transition: transform 0.2s ease, color 0.2s ease;
            flex-shrink: 0;
            margin-left: 8px;
        }

        .link-item-row:hover .link-item-arrow {
            transform: translateX(3px);
            color: var(--bps-mid);
        }

        /* PLACEHOLDER SLOTS (EMPTY ROWS SEEN IN ATTACHMENT 1) */
        .slot-placeholder {
            background: rgba(255, 255, 255, 0.65);
            border: 1px dashed rgba(148, 163, 184, 0.45);
            border-radius: 14px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #94A3B8;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .slot-placeholder:hover {
            background: rgba(255, 255, 255, 0.95);
            border-color: #94A3B8;
            color: #475569;
        }

        /* 4. BOTTOM BAR - AKSES CEPAT (ATTACHMENT 1) */
        .bottom-bar {
            background: var(--dark-bar);
            background: linear-gradient(90deg, #0B1E33 0%, #102A43 50%, #0B1E33 100%);
            padding: 18px 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: white;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.15);
            border-top: 2px solid var(--bps-orange);
            flex-wrap: wrap;
            gap: 20px;
        }

        .bottom-left-group {
            display: flex;
            align-items: center;
            gap: 24px;
            flex-wrap: wrap;
        }

        .akses-cepat-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.3px;
        }

        .megaphone-icon {
            font-size: 30px;
            filter: drop-shadow(0 2px 8px rgba(247, 127, 0, 0.4));
            animation: bounce 3s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-4px); }
            60% { transform: translateY(-2px); }
        }

        .action-buttons-group {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .btn-action-pill {
            background: #FFFFFF;
            color: #0F172A;
            padding: 13px 36px;
            border-radius: 40px;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.6px;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-action-pill:hover {
            background: #F8FAFC;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
            color: var(--bps-dark);
        }

        .btn-action-pill:active {
            transform: translateY(0);
        }

        .bottom-right-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #94A3B8;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            transition: color 0.2s;
        }

        .bottom-right-link:hover {
            color: #FFFFFF;
        }

        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .cards-grid {
                grid-template-columns: 1fr;
            }
            .hero-banner {
                grid-template-columns: 1fr;
                padding: 24px;
                text-align: center;
            }
            .hero-illustration {
                display: none;
            }
            .top-navbar {
                padding: 12px 20px;
            }
            .bottom-bar {
                padding: 16px 20px;
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>

    <!-- 1. TOP NAVBAR -->
    <header class="top-navbar">
        <a href="{{ route('admin.dashboard') }}" class="navbar-brand">
            <img src="{{ asset('images/bps_logo.svg') }}" alt="Badan Pusat Statistik" class="navbar-logo-img">
        </a>
        <div class="navbar-right">
            <div class="navbar-titles">
                <div class="navbar-title">Pembinaan Jabatan Fungsional</div>
                <div class="navbar-subtitle">Biro SDM - BPS</div>
            </div>

            <!-- USER ACTIONS -->
            <div class="user-action-pill">
                <span class="user-badge-role">ADMIN</span>
                <span class="user-name-text">{{ Auth::user()->name }}</span>
                <a href="{{ route('home') }}" class="btn-portal-link" title="Buka Portal Pegawai">
                    🌐 Portal Pegawai ↗
                </a>
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-logout" title="Keluar">
                        🚪 Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- 2. HERO WELCOME BANNER (ATTACHMENT 1) -->
    <section class="hero-banner">
        <div class="hero-content">
            <div class="welcome-badge">
                👋 Selamat Datang
            </div>
            <h1 class="hero-heading">
                Admin Pembinaan<br>
                <span>Jabatan Fungsional</span>
            </h1>
            <p class="hero-description">
                Bersama membina, mengembangkan, dan mewujudkan ASN yang profesional.
            </p>
        </div>
        <div class="hero-illustration">
            <img src="{{ asset('images/bps_staff.jpg') }}" alt="Pegawai BPS RI" class="staff-img">
        </div>
    </section>

    <!-- 3. MAIN CARDS CONTAINER (3 COLUMNS: INTERNAL, EKSTERNAL, LAINNYA) -->
    <main class="cards-container">
        <div class="cards-grid">

            <!-- CARD 1: LINK INTERNAL (BLUE THEME) -->
            <div class="link-card card-blue">
                <div class="card-header-row">
                    <div class="card-header-icon icon-blue-bg">
                        🌐
                    </div>
                    <div class="card-header-title">Link Internal</div>
                </div>

                <div class="link-items-list">
                    <!-- Primary Item: E-TTD BPS -->
                    <a href="{{ $linkInternal[0]['url'] }}" target="_blank" rel="noopener noreferrer" class="link-item-row" title="{{ $linkInternal[0]['desc'] }}">
                        <div class="link-item-left">
                            <div class="link-item-icon" style="background: #E0F2FE; color: #0284C7;">🖋️</div>
                            <span class="link-item-text">{{ $linkInternal[0]['title'] }}</span>
                        </div>
                        <span class="link-item-arrow">›</span>
                    </a>

                    <!-- Additional items (can be customized by user) -->
                    @for ($i = 1; $i < count($linkInternal); $i++)
                        <a href="{{ $linkInternal[$i]['url'] }}" target="_blank" rel="noopener noreferrer" class="link-item-row" title="{{ $linkInternal[$i]['desc'] }}">
                            <div class="link-item-left">
                                <div class="link-item-icon" style="background: #F0F9FF; color: #0369A1;">🔗</div>
                                <span class="link-item-text">{{ $linkInternal[$i]['title'] }}</span>
                            </div>
                            <span class="link-item-arrow">›</span>
                        </a>
                    @endfor

                    <!-- Empty Placeholder Slot as in Attachment 1 -->
                    <div class="slot-placeholder">
                        + Tambah Link Internal (Dummy Slot)
                    </div>
                </div>
            </div>

            <!-- CARD 2: LINK EKSTERNAL (GREEN THEME) -->
            <div class="link-card card-green">
                <div class="card-header-row">
                    <div class="card-header-icon icon-green-bg">
                        🌐
                    </div>
                    <div class="card-header-title">Link Eksternal</div>
                </div>

                <div class="link-items-list">
                    <!-- Item 1: SIASN -->
                    <a href="{{ $linkEksternal[0]['url'] }}" target="_blank" rel="noopener noreferrer" class="link-item-row" title="{{ $linkEksternal[0]['desc'] }}">
                        <div class="link-item-left">
                            <div class="link-item-icon" style="background: #DCFCE7; color: #16A34A;">💻</div>
                            <span class="link-item-text">{{ $linkEksternal[0]['title'] }}</span>
                        </div>
                        <span class="link-item-arrow">›</span>
                    </a>

                    <!-- Item 2: BKN -->
                    <a href="{{ $linkEksternal[1]['url'] }}" target="_blank" rel="noopener noreferrer" class="link-item-row" title="{{ $linkEksternal[1]['desc'] }}">
                        <div class="link-item-left">
                            <div class="link-item-icon" style="background: #F0FDF4; color: #15803D;">🏛️</div>
                            <span class="link-item-text">{{ $linkEksternal[1]['title'] }}</span>
                        </div>
                        <span class="link-item-arrow">›</span>
                    </a>

                    <!-- Additional items -->
                    @for ($i = 2; $i < count($linkEksternal); $i++)
                        <a href="{{ $linkEksternal[$i]['url'] }}" target="_blank" rel="noopener noreferrer" class="link-item-row" title="{{ $linkEksternal[$i]['desc'] }}">
                            <div class="link-item-left">
                                <div class="link-item-icon" style="background: #F0FDF4; color: #16A34A;">🌐</div>
                                <span class="link-item-text">{{ $linkEksternal[$i]['title'] }}</span>
                            </div>
                            <span class="link-item-arrow">›</span>
                        </a>
                    @endfor

                    <!-- Empty Placeholder Slot as in Attachment 1 -->
                    <div class="slot-placeholder">
                        + Tambah Link Eksternal (Dummy Slot)
                    </div>
                </div>
            </div>

            <!-- CARD 3: LAINNYA (ORANGE THEME) -->
            <div class="link-card card-orange">
                <div class="card-header-row">
                    <div class="card-header-icon icon-orange-bg">
                        🌐
                    </div>
                    <div class="card-header-title">Lainnya</div>
                </div>

                <div class="link-items-list">
                    @php
                        $icons = ['TEMPLATE SURAT' => '✉️', 'PERATURAN JF' => '⚖️', 'PENETAPAN JF' => '🎖️', 'REKOMENDASI JF' => '🎯', 'ANALISIS BEBAN KERJA' => '📊', 'MATRIKS KOMPETENSI' => '📋'];
                    @endphp

                    @foreach ($linkLainnya as $item)
                        <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="link-item-row" title="{{ $item['desc'] }}">
                            <div class="link-item-left">
                                <div class="link-item-icon" style="background: #FFEDD5; color: #EA580C;">
                                    {{ $icons[$item['title']] ?? '📁' }}
                                </div>
                                <span class="link-item-text">{{ $item['title'] }}</span>
                            </div>
                            <span class="link-item-arrow">›</span>
                        </a>
                    @endforeach

                    <!-- Empty Placeholder Slot as in Attachment 1 -->
                    <div class="slot-placeholder">
                        + Tambah Link Lainnya (Dummy Slot)
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- 4. BOTTOM BAR - AKSES CEPAT (ATTACHMENT 1) -->
    <footer class="bottom-bar">
        <div class="bottom-left-group">
            <div class="akses-cepat-title">
                <span class="megaphone-icon">📢</span>
                <span>Akses Cepat:</span>
            </div>

            <div class="action-buttons-group">
                <!-- BUTTON 1: FORM USULAN (Opens in new tab) -->
                <a href="{{ $aksesCepat['form_usulan'] }}" target="_blank" rel="noopener noreferrer" class="btn-action-pill">
                    <span>FORM USULAN</span>
                    <span style="font-size:12px; opacity:0.7;">↗</span>
                </a>

                <!-- BUTTON 2: RESPONSE (Opens in new tab) -->
                <a href="{{ $aksesCepat['response'] }}" target="_blank" rel="noopener noreferrer" class="btn-action-pill">
                    <span>RESPONSE</span>
                    <span style="font-size:12px; opacity:0.7;">↗</span>
                </a>
            </div>
        </div>

        <a href="{{ route('home') }}" class="bottom-right-link">
            <span>Beralih ke Tampilan Portal Pegawai</span>
            <span>→</span>
        </a>
    </footer>

</body>
</html>
