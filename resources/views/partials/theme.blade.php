{{-- Tema dark/light (FR-DASH-6): default prefers-color-scheme, pilihan disimpan di localStorage.
     Token warna dan komponen ada di public/css/sijabfu.css. --}}
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
<link rel="stylesheet" href="{{ asset('css/sijabfu.css') }}">
