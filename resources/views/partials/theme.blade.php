{{-- Theme dark/light (FR-DASH-6): token warna netral pada [data-theme], default prefers-color-scheme, disimpan di localStorage. --}}
<script>
    (function () {
        var theme = null;
        try { theme = localStorage.getItem('sijabfu-theme'); } catch (e) {}
        if (theme !== 'light' && theme !== 'dark') {
            theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.setAttribute('data-theme', theme);
    })();

    function toggleTheme() {
        var root = document.documentElement;
        var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        root.setAttribute('data-theme', next);
        try { localStorage.setItem('sijabfu-theme', next); } catch (e) {}
    }
</script>
<style>
    :root,
    [data-theme="light"] {
        color-scheme: light;
        --c-bg: #F0F4F8;
        --c-surface: #FFFFFF;
        --c-surface-2: #F8FAFC;
        --c-surface-3: #F1F5F9;
        --c-border: #E2E8F0;
        --c-border-soft: #F1F5F9;
        --c-border-strong: #CBD5E1;
        --c-text: #1E293B;
        --c-text-2: #475569;
        --c-text-muted: #64748B;
        --c-text-faint: #94A3B8;
        /* judul/teks aksen biru tua, kartu hero terang, kartu login */
        --c-heading: #003B73;
        --c-hero-bg: linear-gradient(135deg, #E0F2FE 0%, #EFF6FF 55%, #DBEAFE 100%);
        --c-card-muted-bg: linear-gradient(145deg, #E2E8F0 0%, #CBD5E1 100%);
    }

    [data-theme="dark"] {
        color-scheme: dark;
        --c-bg: #0B1220;
        --c-surface: #111A2E;
        --c-surface-2: #16213A;
        --c-surface-3: #1C2944;
        --c-border: #26354F;
        --c-border-soft: #1F2C46;
        --c-border-strong: #33456A;
        --c-text: #E2E8F0;
        --c-text-2: #CBD5E1;
        --c-text-muted: #94A3B8;
        --c-text-faint: #7C8BA5;
        --c-heading: #93C5FD;
        --c-hero-bg: linear-gradient(135deg, #0F1E36 0%, #132846 55%, #16305A 100%);
        --c-card-muted-bg: linear-gradient(145deg, #1C2944 0%, #26354F 100%);
    }

    .theme-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 1px solid var(--c-border);
        background: var(--c-surface-2);
        color: var(--c-text);
        cursor: pointer;
        font-size: 16px;
    }

    [data-theme="dark"] .top-header-breadcrumb a { color: var(--c-heading); }

    .theme-toggle .icon-dark { display: none; }
    [data-theme="dark"] .theme-toggle .icon-light { display: none; }
    [data-theme="dark"] .theme-toggle .icon-dark { display: inline; }
</style>
