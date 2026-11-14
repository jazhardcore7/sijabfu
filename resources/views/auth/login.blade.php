<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIGMA JF BPS RI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bps-dark: #003B73;
            --bps-mid: #0077B6;
            --bps-cyan: #00A3E0;
            --bps-orange: #F77F00;
            --bps-green: #70B82C;
            --bg-slate: #F8FAFC;
            --card-gray: #CBD5E1;
            --btn-purple: #6366F1;
            --btn-purple-hover: #4F46E5;
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

        /* TOP NAVBAR */
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

        /* HERO WELCOME BANNER */
        .hero-banner {
            position: relative;
            background: linear-gradient(135deg, #E0F2FE 0%, #EFF6FF 60%, #DBEAFE 100%);
            padding: 32px 48px;
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            align-items: center;
            border-bottom: 1px solid #E2E8F0;
            overflow: hidden;
        }

        .hero-banner::before {
            content: '';
            position: absolute;
            top: -60px;
            left: -40px;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, 0.45);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-banner::after {
            content: '';
            position: absolute;
            bottom: -50px;
            right: 20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(186, 230, 253, 0.5) 0%, transparent 70%);
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
            font-size: 32px;
            font-weight: 800;
            color: var(--bps-dark);
            line-height: 1.25;
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
            max-height: 200px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 8px 16px rgba(0, 59, 115, 0.15));
        }

        /* MAIN CONTENT AREA */
        .main-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 24px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .login-grid {
            display: grid;
            grid-template-columns: 1fr 1.15fr;
            align-items: center;
            gap: 60px;
            width: 100%;
        }

        /* LEFT BRAND COLUMN */
        .brand-showcase {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 20px;
        }

        .brand-showcase-img {
            max-width: 320px;
            width: 100%;
            height: auto;
            filter: drop-shadow(0 10px 25px rgba(2, 132, 199, 0.12));
            transition: transform 0.3s ease;
        }

        .brand-showcase-img:hover {
            transform: scale(1.02);
        }

        /* RIGHT FORM COLUMN */
        .login-card {
            background: #E2E8F0;
            background: linear-gradient(145deg, #E2E8F0 0%, #CBD5E1 100%);
            border-radius: 28px;
            padding: 40px 44px;
            box-shadow: 0 10px 30px rgba(100, 116, 139, 0.2);
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.6);
        }

        .login-card-title {
            font-size: 16.5px;
            font-weight: 700;
            color: #1E293B;
            margin-bottom: 24px;
            text-align: left;
        }

        .alert-error {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #991B1B;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-info {
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            color: #1E40AF;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .input-group {
            margin-bottom: 18px;
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-box {
            position: absolute;
            left: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0F172A;
            z-index: 2;
        }

        .form-control {
            width: 100%;
            background: #FFFFFF;
            border: 1.5px solid transparent;
            border-radius: 12px;
            padding: 16px 16px 16px 52px;
            font-size: 14.5px;
            color: #1E293B;
            outline: none;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        }

        .form-control::placeholder {
            color: #64748B;
            font-size: 14px;
        }

        .form-control:focus {
            background: #FFFFFF;
            border-color: #6366F1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            background: none;
            border: none;
            color: #64748B;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-login {
            width: 100%;
            background: #6366F1;
            background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.5px;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 8px;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #4F46E5 0%, #4338CA 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(99, 102, 241, 0.5);
        }

        .btn-login:active {
            transform: translateY(1px);
        }

        .btn-sso {
            width: 100%;
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: #FFFFFF;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.5px;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-sso:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
        }

        .btn-sso:active {
            transform: translateY(1px);
        }

        /* DEMO QUICK LOGIN ACCORDION / CHIPS */
        .demo-box {
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px dashed rgba(100, 116, 139, 0.3);
        }

        .demo-label {
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .demo-buttons-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        @media (max-width: 640px) {
            .demo-buttons-row {
                grid-template-columns: 1fr;
            }
        }

        .btn-demo-chip {
            background: #FFFFFF;
            border: 1px solid #CBD5E1;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 700;
            color: #1E293B;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 2px;
            transition: all 0.2s ease;
            text-align: left;
        }

        .btn-demo-chip:hover {
            border-color: #6366F1;
            background: #F8FAFC;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .btn-demo-chip .chip-title {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--bps-dark);
            font-size: 12px;
        }

        .btn-demo-chip .chip-sub {
            font-size: 11px;
            color: #64748B;
            font-weight: 500;
        }

        /* FOOTER */
        .footer-bar {
            background: #002855;
            background: linear-gradient(90deg, #001F42 0%, #002B5C 50%, #001F42 100%);
            color: #FFFFFF;
            padding: 16px 24px;
            text-align: center;
            font-size: 14.5px;
            font-weight: 700;
            letter-spacing: 0.3px;
            border-top: 2px solid var(--bps-orange);
        }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .hero-banner {
                grid-template-columns: 1fr;
                padding: 28px 24px;
                text-align: center;
            }
            .hero-illustration {
                display: none;
            }
            .top-navbar {
                padding: 12px 20px;
            }
            .login-grid {
                grid-template-columns: 1fr;
                gap: 32px;
            }
            .brand-showcase {
                padding: 0;
            }
            .brand-showcase-img {
                max-width: 200px;
            }
            .login-card {
                padding: 30px 24px;
            }
        }
    </style>
</head>
<body>

    <!-- 1. TOP NAVBAR -->
    <header class="top-navbar">
        <a href="{{ route('login') }}" class="navbar-brand">
            <img src="{{ asset('images/bps_logo.svg') }}" alt="Badan Pusat Statistik" class="navbar-logo-img">
        </a>
        <div class="navbar-right">
            <div class="navbar-title">SIGMA JF</div>
            <div class="navbar-subtitle">Biro SDM - BPS</div>
        </div>
    </header>

    <!-- 2. HERO WELCOME BANNER (ATTACHMENT 2) -->
    <section class="hero-banner">
        <div class="hero-content">
            <div class="welcome-badge">
                👋 Selamat Datang
            </div>
            <h1 class="hero-heading">
                di Portal Pembinaan<br>
                <span>SIGMA JF</span>
            </h1>
            <p class="hero-description">
                Bersama membina, mengembangkan, dan mewujudkan ASN yang profesional.
            </p>
        </div>
        <div class="hero-illustration">
            <img src="{{ asset('images/bps_staff.jpg') }}" alt="Pegawai BPS RI" class="staff-img">
        </div>
    </section>

    <!-- 3. MAIN LOGIN AREA -->
    <main class="main-container">
        <div class="login-grid">
            
            <!-- LEFT: BIG BPS STACKED LOGO -->
            <div class="brand-showcase">
                <img src="{{ asset('images/bps_emblem_stacked.svg') }}" alt="Logo Resmi BPS RI" class="brand-showcase-img">
            </div>

            <!-- RIGHT: LOGIN CARD CONTAINER -->
            <div class="login-card">
                <div class="login-card-title">
                    Silahkan Masukkan NIP dan Password Anda
                </div>

                @if ($errors->any())
                    <div class="alert-error">
                        <span>⚠️</span>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                @if (session('info'))
                    <div class="alert-info">
                        <span>ℹ️</span>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" id="loginForm">
                    @csrf
                    
                    <!-- INPUT NIP -->
                    <div class="input-group">
                        <div class="input-icon-box">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            name="nip" 
                            id="nipInput" 
                            class="form-control" 
                            placeholder="Masukkan NIP Anda" 
                            value="{{ old('nip') }}" 
                            required 
                            autocomplete="username"
                            autofocus>
                    </div>

                    <!-- INPUT PASSWORD -->
                    <div class="input-group">
                        <div class="input-icon-box">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </div>
                        <input 
                            type="password" 
                            name="password" 
                            id="passwordInput" 
                            class="form-control" 
                            placeholder="Masukkan Password Anda" 
                            required 
                            autocomplete="current-password">
                        <button type="button" class="password-toggle" onclick="togglePasswordVisibility()" title="Lihat Password">
                            <svg id="eyeIcon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>

                    <!-- SUBMIT BUTTON -->
                    <button type="submit" class="btn-login" id="submitBtn">
                        LOGIN
                    </button>

                    <!-- BUTTON LOGIN SSO (WARNA HIJAU) -->
                    <button type="button" class="btn-sso" id="btnSso" onclick="handleSSO()">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                        <span>LOGIN SSO</span>
                    </button>
                </form>

                <!-- DEMO ACCOUNT SHORTCUTS -->
                <div class="demo-box">
                    <div class="demo-label">
                        <span>⚡</span>
                        <span>Klik untuk Coba Akun Demo:</span>
                    </div>
                    <div class="demo-buttons-row">
                        <button type="button" class="btn-demo-chip" onclick="fillCredentials('198501012010011001', 'password')">
                            <span class="chip-title">🛡️ Admin PIC</span>
                            <span class="chip-sub">Monitoring & Kelola</span>
                        </button>
                        <button type="button" class="btn-demo-chip" onclick="fillCredentials('199505152020121002', 'password')">
                            <span class="chip-title">👤 Sudah Usul</span>
                            <span class="chip-sub">Budi (Cek Progres)</span>
                        </button>
                        <button type="button" class="btn-demo-chip" onclick="fillCredentials('199808182022032005', 'password')">
                            <span class="chip-title">📝 Belum Usul</span>
                            <span class="chip-sub">Siti (Coba Isi Form)</span>
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <!-- 4. FOOTER BAR -->
    <footer class="footer-bar">
        2026 – Tim Pembinaan JF BPS RI
    </footer>

    <script>
        function fillCredentials(nip, password) {
            document.getElementById('nipInput').value = nip;
            document.getElementById('passwordInput').value = password;
            // Highlight effect
            const btn = document.getElementById('submitBtn');
            btn.style.transform = 'scale(1.02)';
            setTimeout(() => { btn.style.transform = 'none'; }, 200);
        }

        function handleSSO() {
            const ssoBtn = document.getElementById('btnSso');
            ssoBtn.innerHTML = '<span>⏳ Mengautentikasi SSO BPS...</span>';
            ssoBtn.style.opacity = '0.85';
            setTimeout(() => {
                fillCredentials('199505152020121002', 'password');
                document.getElementById('loginForm').submit();
            }, 350);
        }

        function togglePasswordVisibility() {
            const pwd = document.getElementById('passwordInput');
            const icon = document.getElementById('eyeIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />';
            } else {
                pwd.type = 'password';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
            }
        }
    </script>
</body>
</html>
