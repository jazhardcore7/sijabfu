<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SIGMA JF BPS Republik Indonesia - Pusat. Layanan pengusulan, monitoring progres berkas, dan pembinaan jabatan fungsional.">
    <meta name="keywords" content="BPS, Jabatan Fungsional, SIGMA JF, Portal Layanan">
    <title>SIGMA JF - BPS RI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
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
            overflow-x: hidden;
        }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: var(--bps-mid); border-radius: 3px; }

        /* ===== HEADER ===== */
        .landing-header {
            background: linear-gradient(135deg, var(--bps-dark) 0%, #00509D 100%);
            padding: 0 48px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 24px rgba(0,0,0,0.2);
        }

        .header-inner {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 72px;
            gap: 20px;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .header-logo {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--bps-orange), #F59E0B);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 16px;
            color: white;
            box-shadow: 0 4px 16px rgba(247,127,0,0.5);
            flex-shrink: 0;
        }

        .header-text-main {
            font-size: 14px;
            font-weight: 700;
            color: white;
            line-height: 1.2;
        }

        .header-text-sub {
            font-size: 11px;
            color: rgba(255,255,255,0.65);
            font-weight: 400;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .header-datetime {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 10px;
            padding: 8px 16px;
            color: rgba(255,255,255,0.9);
            font-size: 13px;
            font-weight: 500;
            backdrop-filter: blur(8px);
        }

        /* ===== HERO ===== */
        .hero-section {
            background: linear-gradient(135deg, var(--bps-dark) 0%, #00509D 50%, var(--bps-mid) 100%);
            padding: 80px 48px 100px;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(144,224,239,0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(247,127,0,0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-inner {
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(247,127,0,0.2);
            border: 1px solid rgba(247,127,0,0.4);
            color: #FCD34D;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .hero-title {
            font-size: clamp(28px, 4vw, 48px);
            font-weight: 900;
            color: white;
            line-height: 1.15;
            margin-bottom: 20px;
            max-width: 700px;
        }

        .hero-title span {
            color: var(--bps-orange);
        }

        .hero-desc {
            font-size: 16px;
            color: rgba(255,255,255,0.75);
            max-width: 600px;
            line-height: 1.7;
            margin-bottom: 36px;
        }

        .hero-stats {
            display: flex;
            gap: 32px;
            flex-wrap: wrap;
        }

        .hero-stat {
            text-align: center;
        }

        .hero-stat-number {
            font-size: 28px;
            font-weight: 900;
            color: white;
        }

        .hero-stat-label {
            font-size: 12px;
            color: rgba(255,255,255,0.6);
            font-weight: 500;
        }

        /* ===== WAVE DIVIDER ===== */
        .wave-divider {
            height: 60px;
            background: var(--bg-slate);
            position: relative;
            overflow: hidden;
            margin-top: -1px;
        }

        .wave-divider svg {
            position: absolute;
            bottom: 0;
            width: 100%;
        }

        /* ===== CARDS SECTION ===== */
        .cards-section {
            padding: 60px 48px 80px;
            background: var(--bg-slate);
        }

        .section-header {
            text-align: center;
            max-width: 1400px;
            margin: 0 auto 48px;
        }

        .section-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(0,119,182,0.08);
            color: var(--bps-mid);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .section-title {
            font-size: clamp(22px, 3vw, 36px);
            font-weight: 800;
            color: var(--bps-dark);
            margin-bottom: 12px;
        }

        .section-desc {
            font-size: 15px;
            color: #64748b;
        }

        /* Cards Grid: 5 responsive */
        .cards-grid {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .cards-grid .card-item:nth-child(4) {
            grid-column: 1 / 2;
        }

        .cards-grid .card-item:nth-child(5) {
            grid-column: 2 / 3;
        }

        /* Override for centering last 2 */
        .cards-bottom-row {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
            padding: 0 calc(16.666% + 12px);
        }

        .cards-wrapper {
            max-width: 1400px;
            margin: 0 auto;
        }

        .cards-row-top {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-bottom: 24px;
        }

        .cards-row-bottom {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
            max-width: calc(66.666% - 8px);
            margin: 0 auto;
        }

        /* Service Card */
        .service-card {
            background: white;
            border-radius: 20px;
            padding: 32px 28px;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            gap: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .service-card:hover::before { opacity: 1; }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.12);
            border-color: transparent;
        }

        /* Color variants */
        .card-blue::before { background: linear-gradient(90deg, var(--bps-dark), var(--bps-mid)); }
        .card-blue:hover { box-shadow: 0 20px 40px rgba(0,59,115,0.15); }

        .card-indigo::before { background: linear-gradient(90deg, #4F46E5, #7C3AED); }
        .card-indigo:hover { box-shadow: 0 20px 40px rgba(79,70,229,0.15); }

        .card-orange::before { background: linear-gradient(90deg, var(--bps-orange), #F59E0B); }
        .card-orange:hover { box-shadow: 0 20px 40px rgba(247,127,0,0.15); }

        .card-teal::before { background: linear-gradient(90deg, #0D9488, #0891B2); }
        .card-teal:hover { box-shadow: 0 20px 40px rgba(13,148,136,0.15); }

        .card-purple::before { background: linear-gradient(90deg, #7C3AED, #EC4899); }
        .card-purple:hover { box-shadow: 0 20px 40px rgba(124,58,237,0.15); }

        .card-icon-wrapper {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            flex-shrink: 0;
        }

        .icon-blue { background: linear-gradient(135deg, rgba(0,59,115,0.1), rgba(0,119,182,0.1)); }
        .icon-indigo { background: linear-gradient(135deg, rgba(79,70,229,0.1), rgba(124,58,237,0.1)); }
        .icon-orange { background: linear-gradient(135deg, rgba(247,127,0,0.1), rgba(245,158,11,0.1)); }
        .icon-teal { background: linear-gradient(135deg, rgba(13,148,136,0.1), rgba(8,145,178,0.1)); }
        .icon-purple { background: linear-gradient(135deg, rgba(124,58,237,0.1), rgba(236,72,153,0.1)); }

        .card-number {
            position: absolute;
            top: 20px;
            right: 24px;
            font-size: 48px;
            font-weight: 900;
            color: #f1f5f9;
            line-height: 1;
            pointer-events: none;
        }

        .card-content { flex: 1; }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--bps-dark);
            margin-bottom: 8px;
        }

        .card-desc {
            font-size: 13.5px;
            color: #64748b;
            line-height: 1.6;
        }

        .card-arrow {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--bps-mid);
            transition: gap 0.2s;
        }

        .service-card:hover .card-arrow { gap: 10px; }
        .card-blue .card-arrow { color: var(--bps-dark); }
        .card-indigo .card-arrow { color: #4F46E5; }
        .card-orange .card-arrow { color: var(--bps-orange); }
        .card-teal .card-arrow { color: #0D9488; }
        .card-purple .card-arrow { color: #7C3AED; }

        /* ===== FOOTER ===== */
        .landing-footer {
            background: linear-gradient(135deg, var(--bps-dark), #002855);
            padding: 40px 48px;
            text-align: center;
        }

        .footer-inner {
            max-width: 1400px;
            margin: 0 auto;
        }

        .footer-logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .footer-logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--bps-orange), #F59E0B);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 12px;
            color: white;
        }

        .footer-logo-text {
            font-size: 14px;
            font-weight: 600;
            color: rgba(255,255,255,0.8);
        }

        .footer-copyright {
            font-size: 13px;
            color: rgba(255,255,255,0.45);
            line-height: 1.6;
        }

        .footer-divider {
            width: 60px;
            height: 2px;
            background: linear-gradient(90deg, var(--bps-orange), var(--bps-mid));
            border-radius: 1px;
            margin: 16px auto;
        }

        /* ANIMATIONS */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .animate-fade-in { animation: fadeInUp 0.6s ease forwards; }
        .animate-slide-left { animation: slideInLeft 0.6s ease forwards; }
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
        .delay-500 { animation-delay: 0.5s; }
        .delay-600 { animation-delay: 0.6s; }

        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .landing-header { padding: 0 24px; }
            .hero-section { padding: 60px 24px 80px; }
            .cards-section { padding: 40px 24px 60px; }
            .landing-footer { padding: 32px 24px; }
            .cards-row-top { grid-template-columns: 1fr 1fr; }
            .cards-row-bottom { max-width: 100%; grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 640px) {
            .header-datetime { display: none; }
            .header-text-main { font-size: 12px; }
            .hero-stats { gap: 20px; }
            .cards-row-top { grid-template-columns: 1fr; }
            .cards-row-bottom { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header class="landing-header">
    <div class="header-inner">
        <div class="header-brand">
            <div class="header-logo">BPS</div>
            <div>
                <div class="header-text-main">SIGMA JF</div>
                <div class="header-text-sub">Badan Pusat Statistik Republik Indonesia</div>
            </div>
        </div>
        <div class="header-right" style="display:flex; align-items:center; gap:16px;">
            <div class="header-datetime">
                📅 <span id="headerDatetime">--</span>
            </div>
            @auth
            <div style="display:flex; align-items:center; gap:10px; background:rgba(255,255,255,0.12); padding:5px 14px; border-radius:30px; border:1px solid rgba(255,255,255,0.25);">
                <span style="color:white; font-size:12.5px; font-weight:700;">👤 {{ Auth::user()->name }}</span>
                @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" style="color:#fde047; font-size:11.5px; font-weight:700; text-decoration:none; background:rgba(0,0,0,0.25); padding:3px 8px; border-radius:10px;">
                    🛡️ Admin
                </a>
                @endif
                <form action="{{ route('logout') }}" method="POST" style="display:inline; margin:0;">
                    @csrf
                    <button type="submit" style="background:none; border:none; color:#fca5a5; font-size:12px; font-weight:700; cursor:pointer; padding:2px 6px;">
                        Keluar 🚪
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </div>
</header>

<!-- HERO SECTION -->
<section class="hero-section">
    <div class="hero-inner">
        <div class="hero-badge animate-fade-in" style="opacity:0">
            🏛️ BPS Republik Indonesia
        </div>
        <h1 class="hero-title animate-fade-in delay-100" style="opacity:0">
            Portal Layanan<br>
            <span>SIGMA JF</span><br>
            BPS RI Pusat
        </h1>
        <p class="hero-desc animate-fade-in delay-200" style="opacity:0">
            Selamat datang di SIGMA JF Badan Pusat Statistik Republik Indonesia.
            Kami hadir untuk memudahkan proses pengusulan, pembinaan, dan pengembangan
            Jabatan Fungsional di lingkungan BPS RI.
        </p>
        <div class="hero-stats animate-fade-in delay-300" style="opacity:0">
            <div class="hero-stat">
                <div class="hero-stat-number">500+</div>
                <div class="hero-stat-label">Pejabat Fungsional</div>
            </div>
            <div style="width: 1px; background: rgba(255,255,255,0.2); height: 40px; align-self: center;"></div>
            <div class="hero-stat">
                <div class="hero-stat-number">33</div>
                <div class="hero-stat-label">Jabatan Fungsional</div>
            </div>
            <div style="width: 1px; background: rgba(255,255,255,0.2); height: 40px; align-self: center;"></div>
            <div class="hero-stat">
                <div class="hero-stat-number">5</div>
                <div class="hero-stat-label">Layanan Tersedia</div>
            </div>
            <div style="width: 1px; background: rgba(255,255,255,0.2); height: 40px; align-self: center;"></div>
            <div class="hero-stat">
                <div class="hero-stat-number">2026</div>
                <div class="hero-stat-label">Tahun Aktif</div>
            </div>
        </div>
    </div>
</section>

<!-- WAVE DIVIDER -->
<div style="background: linear-gradient(135deg, var(--bps-dark), var(--bps-mid)); margin: 0;">
    <svg viewBox="0 0 1440 60" xmlns="http://www.w3.org/2000/svg" style="display:block; width:100%;">
        <path d="M0,60 C360,0 1080,60 1440,20 L1440,60 L0,60 Z" fill="#F0F4F8"/>
    </svg>
</div>

<!-- CARDS SECTION -->
<section class="cards-section">
    <div class="section-header animate-fade-in" style="opacity:0;">
        <div class="section-label">🗂️ Layanan Kami</div>
        <h2 class="section-title">5 Modul Layanan Utama</h2>
        <p class="section-desc">Pilih layanan yang Anda butuhkan untuk memulai proses pengusulan dan pembinaan jabatan fungsional</p>
    </div>

    <div class="cards-wrapper">
        <!-- Row 1: 3 cards -->
        <div class="cards-row-top">
            <a href="{{ route('alur-layanan') }}" class="service-card card-blue animate-fade-in delay-100" style="opacity:0">
                <div class="card-number">01</div>
                <div class="card-icon-wrapper icon-blue">🔄</div>
                <div class="card-content">
                    <div class="card-title">Alur Layanan</div>
                    <div class="card-desc">Panduan step-by-step proses pengusulan Jabatan Fungsional dari awal hingga terbitnya SK Pengangkatan.</div>
                </div>
                <div class="card-arrow">Lihat Alur <span>→</span></div>
            </a>

            <a href="{{ route('form-usulan') }}" class="service-card card-indigo animate-fade-in delay-200" style="opacity:0">
                <div class="card-number">02</div>
                <div class="card-icon-wrapper icon-indigo">📝</div>
                <div class="card-content">
                    <div class="card-title">Form Usulan</div>
                    <div class="card-desc">Formulir pengajuan resmi Jabatan Fungsional secara online. Isi dan kirim dokumen usulan Anda dengan mudah.</div>
                </div>
                <div class="card-arrow">Isi Formulir <span>→</span></div>
            </a>

            <a href="{{ route('progress-berkas') }}" class="service-card card-orange animate-fade-in delay-300" style="opacity:0">
                <div class="card-number">03</div>
                <div class="card-icon-wrapper icon-orange">📊</div>
                <div class="card-content">
                    <div class="card-title">Progress Berkas</div>
                    <div class="card-desc">Pantau posisi berkas usulan jabatan fungsional Anda secara real-time dari tahap pengusulan hingga selesai.</div>
                </div>
                <div class="card-arrow">Pantau Berkas <span>→</span></div>
            </a>
        </div>

        <!-- Row 2: 2 cards centered -->
        <div class="cards-row-bottom">
            <a href="{{ route('regulasi') }}" class="service-card card-teal animate-fade-in delay-400" style="opacity:0">
                <div class="card-number">04</div>
                <div class="card-icon-wrapper icon-teal">📋</div>
                <div class="card-content">
                    <div class="card-title">Regulasi</div>
                    <div class="card-desc">Kumpulan peraturan dan regulasi Jabatan Fungsional, termasuk Perka BPS, PermenPAN-RB, dan peraturan BKN.</div>
                </div>
                <div class="card-arrow">Lihat Regulasi <span>→</span></div>
            </a>

            <a href="{{ route('tim-kerja') }}" class="service-card card-purple animate-fade-in delay-500" style="opacity:0">
                <div class="card-number">05</div>
                <div class="card-icon-wrapper icon-purple">👥</div>
                <div class="card-content">
                    <div class="card-title">Tim Kerja</div>
                    <div class="card-desc">Profil lengkap Tim Pembinaan JF BPS RI Pusat beserta bidang tanggung jawab masing-masing.</div>
                </div>
                <div class="card-arrow">Lihat Tim <span>→</span></div>
            </a>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="landing-footer">
    <div class="footer-inner">
        <div class="footer-logo">
            <div class="footer-logo-icon">BPS</div>
            <div class="footer-logo-text">Badan Pusat Statistik Republik Indonesia</div>
        </div>
        <div class="footer-divider"></div>
        <div class="footer-copyright">
            Copyright &copy; 2026 Badan Pusat Statistik RI<br>
            Tim Pembinaan JF
        </div>
    </div>
</footer>

<script>
    function updateDatetime() {
        const now = new Date();
        const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        const dayName = days[now.getDay()];
        const date = now.getDate();
        const month = months[now.getMonth()];
        const year = now.getFullYear();
        const hours = String(now.getHours()).padStart(2,'0');
        const minutes = String(now.getMinutes()).padStart(2,'0');
        const seconds = String(now.getSeconds()).padStart(2,'0');
        document.getElementById('headerDatetime').textContent = `${dayName}, ${date} ${month} ${year} | ${hours}:${minutes}:${seconds}`;
    }

    updateDatetime();
    setInterval(updateDatetime, 1000);

    // Intersection Observer for scroll animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.animate-fade-in').forEach(el => {
        el.style.animationPlayState = 'paused';
        observer.observe(el);
    });
</script>
</body>
</html>
