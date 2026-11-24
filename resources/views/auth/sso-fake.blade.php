<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulasi SSO (Lokal) - SIGMA JF</title>
    @include('partials.theme')
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; background: var(--c-bg); color: var(--c-text); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 16px; }
        .card { width: 100%; max-width: 440px; background: var(--c-surface); border: 1px solid var(--c-border); border-radius: 18px; padding: 28px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; background: #FEF3C7; color: #92400E; font-size: 11.5px; font-weight: 700; margin-bottom: 12px; }
        h1 { font-size: 20px; margin-bottom: 6px; }
        p { font-size: 13px; color: var(--c-text-muted); line-height: 1.5; margin-bottom: 18px; }
        label { font-size: 13px; font-weight: 600; color: var(--c-text-2); display: block; margin-bottom: 6px; }
        input { width: 100%; border: 1.5px solid var(--c-border-strong); border-radius: 10px; padding: 10px 12px; font-size: 14px; background: var(--c-surface); color: var(--c-text); margin-bottom: 16px; }
        button { width: 100%; border: none; border-radius: 10px; padding: 12px; font-weight: 800; color: #FFFFFF; background: linear-gradient(135deg, #10B981, #059669); cursor: pointer; }
        a { display: block; text-align: center; margin-top: 14px; font-size: 13px; color: var(--c-text-muted); }
    </style>
</head>
<body>
    <div class="card" data-section="sso-fake">
        <span class="badge">Mode simulasi SSO (driver fake, hanya lokal)</span>
        <h1>Login SSO BPS</h1>
        <p>Halaman ini menggantikan penyedia SSO selama endpoint SSO asli belum tersedia. Masukkan NIP yang terdaftar.</p>
        <form action="{{ route('sso.callback') }}" method="GET">
            <input type="hidden" name="state" value="{{ $state }}">
            <label for="nip">NIP</label>
            <input type="text" id="nip" name="nip" required autofocus autocomplete="off">
            <button type="submit">Masuk dengan SSO</button>
        </form>
        <a href="{{ route('login') }}">← Kembali ke halaman login</a>
    </div>
</body>
</html>
