<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulasi SSO (Lokal) - SIGMA JF</title>
    @include('partials.theme')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-page">
    <main class="auth-main">
        <div class="card auth-sso-card" data-section="sso-fake">
            <div class="card-body">
                <span class="status-badge" data-status="perbaikan">Mode simulasi SSO (driver fake, hanya lokal)</span>
                <h1 class="auth-title">Login SSO BPS</h1>
                <p class="auth-desc">Halaman ini menggantikan penyedia SSO selama endpoint SSO asli belum tersedia. Masukkan NIP yang terdaftar.</p>
                <form action="{{ route('sso.callback') }}" method="GET">
                    <input type="hidden" name="state" value="{{ $state }}">
                    <div class="field">
                        <label class="field-label" for="nip">NIP</label>
                        <input type="text" id="nip" name="nip" required autofocus autocomplete="off" inputmode="numeric">
                    </div>
                    <button type="submit" class="btn btn--primary btn--block">Masuk dengan SSO</button>
                </form>
                <a href="{{ route('login') }}" class="link-back auth-back"><x-icon name="arrow-left" /> Kembali ke halaman login</a>
            </div>
        </div>
    </main>
</body>
</html>
