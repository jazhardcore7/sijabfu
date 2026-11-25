<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIGMA JF BPS RI</title>
    @include('partials.theme')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-page">

    <header class="auth-topbar">
        <a href="{{ route('login') }}" class="auth-brand">
            <img src="{{ asset('images/bps_logo.svg') }}" alt="Badan Pusat Statistik" class="auth-brand-logo">
        </a>
        <div class="auth-topbar-right">
            <div class="auth-topbar-title">SIGMA JF</div>
            <div class="auth-topbar-sub">Biro SDM - BPS</div>
        </div>
        <button type="button" class="theme-toggle" onclick="toggleTheme()" title="Ganti tema" aria-label="Ganti tema dark/light">
            <span class="icon-light"><x-icon name="moon" /></span>
            <span class="icon-dark"><x-icon name="sun" /></span>
        </button>
    </header>

    <main class="auth-main">
        <div class="auth-card">
            <div class="auth-aside">
                <img src="{{ asset('images/bps_emblem_stacked.svg') }}" alt="Logo Resmi BPS RI" class="auth-emblem">
                <div class="hero-badge">Selamat Datang</div>
                <h1 class="auth-heading">di Portal Pembinaan <span>SIGMA JF</span></h1>
                <p class="auth-desc">Bersama membina, mengembangkan, dan mewujudkan ASN yang profesional.</p>
            </div>

            <div class="auth-body">
                <h2 class="auth-title">
                    {{ $localEnabled ? 'Silahkan Masukkan NIP dan Password Anda' : 'Silahkan Masuk dengan Akun SSO BPS' }}
                </h2>

                @if ($errors->any())
                <x-alert type="error">{{ $errors->first() }}</x-alert>
                @endif

                @if (session('info'))
                <x-alert type="info">{{ session('info') }}</x-alert>
                @endif

                {{-- LOGIN SSO (FR-AUTH-1, FR-AUTH-2): lewat adapter SSO --}}
                <a href="{{ route('sso.redirect') }}" class="btn btn--primary btn--block btn--lg" id="btnSso" data-action="login-sso">
                    <x-icon name="shield" />
                    <span>LOGIN SSO</span>
                </a>

                {{-- FR-AUTH-3: form NIP/password hanya bila AUTH_LOCAL_ENABLED=true --}}
                @if($localEnabled)
                <div class="auth-divider" aria-hidden="true"></div>
                <form action="{{ route('login') }}" method="POST" id="loginForm">
                    @csrf
                    <div class="field">
                        <label class="sr-only" for="nipInput">Masukkan NIP Anda</label>
                        <input type="text" name="nip" id="nipInput" placeholder="Masukkan NIP Anda" value="{{ old('nip') }}" required autocomplete="username" inputmode="numeric" @error('nip') aria-invalid="true" @enderror>
                    </div>
                    <div class="field">
                        <label class="sr-only" for="passwordInput">Masukkan Password Anda</label>
                        <div class="password-wrap">
                            <input type="password" name="password" id="passwordInput" placeholder="Masukkan Password Anda" required autocomplete="current-password">
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility(this)" title="Lihat Password" aria-label="Lihat Password" aria-pressed="false" aria-controls="passwordInput">
                                <x-icon name="eye" />
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn--secondary btn--block" id="submitBtn">LOGIN</button>
                </form>
                @endif
            </div>
        </div>
    </main>

    <footer class="auth-footer">
        2026 – Tim Pembinaan JF BPS RI
    </footer>

    <script>
        function togglePasswordVisibility(btn) {
            const pwd = document.getElementById('passwordInput');
            const show = pwd.type === 'password';
            pwd.type = show ? 'text' : 'password';
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
        }
    </script>
</body>
</html>
