@extends('layouts.app')

@section('title', 'Regulasi')
@section('breadcrumb', 'Regulasi')

@push('styles')
<style>
    .regulasi-container { max-width: 1100px; }

    .regulasi-search {
        display: flex;
        gap: 12px;
        margin-bottom: 28px;
    }

    .search-input-wrapper {
        flex: 1;
        position: relative;
    }

    .search-input {
        width: 100%;
        padding: 12px 16px 12px 44px;
        border: 1.5px solid var(--c-border);
        border-radius: 12px;
        font-size: 14px;
        font-family: inherit;
        color: var(--c-text-2);
        background: var(--c-surface);
        transition: border-color 0.2s, box-shadow 0.2s;
        outline: none;
    }

    .search-input:focus {
        border-color: var(--bps-mid);
        box-shadow: 0 0 0 3px rgba(0,119,182,0.1);
    }

    .search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 16px;
        color: var(--c-text-faint);
    }

    .kategori-filter {
        display: flex;
        gap: 8px;
        margin-bottom: 28px;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 600;
        border: 1.5px solid var(--c-border);
        background: var(--c-surface);
        color: var(--c-text-muted);
        cursor: pointer;
        transition: all 0.2s;
        font-family: inherit;
    }

    .filter-btn:hover, .filter-btn.active {
        background: var(--bps-dark);
        color: white;
        border-color: var(--bps-dark);
    }

    /* REGULASI TABLE CARD */
    .regulasi-group {
        margin-bottom: 28px;
    }

    .group-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 20px;
        background: linear-gradient(135deg, var(--bps-dark), #00509D);
        border-radius: 12px 12px 0 0;
        color: white;
    }

    .group-icon {
        width: 36px;
        height: 36px;
        background: rgba(255,255,255,0.15);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .group-title {
        font-size: 15px;
        font-weight: 700;
    }

    .group-count {
        margin-left: auto;
        background: rgba(255,255,255,0.2);
        padding: 3px 10px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 600;
    }

    .regulasi-table {
        width: 100%;
        border-collapse: collapse;
        background: var(--c-surface);
        border-radius: 0 0 12px 12px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    }

    .regulasi-table th {
        background: var(--c-surface-2);
        padding: 12px 18px;
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        color: var(--c-text-2);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--c-border);
    }

    .regulasi-table td {
        padding: 16px 18px;
        font-size: 14px;
        color: var(--c-text-2);
        border-bottom: 1px solid var(--c-border-soft);
        vertical-align: top;
    }

    .regulasi-table tr:last-child td { border-bottom: none; }

    .regulasi-table tr:hover td { background: var(--c-surface-2); }

    .reg-nomor {
        font-weight: 700;
        color: var(--c-heading);
        font-size: 13px;
    }

    .reg-tentang {
        font-size: 13.5px;
        color: var(--c-text-2);
        line-height: 1.5;
    }

    .tahun-badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        background: rgba(0,59,115,0.08);
        color: var(--c-heading);
        border-radius: 50px;
        font-size: 12px;
        font-weight: 700;
    }

    .download-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        background: linear-gradient(135deg, var(--bps-dark), var(--bps-mid));
        color: white;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .download-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,59,115,0.3);
    }

    .disclaimer-box {
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 12px;
        padding: 16px 20px;
        font-size: 13px;
        color: #92400e;
        display: flex;
        gap: 12px;
        align-items: flex-start;
        margin-bottom: 24px;
    }

    @media (max-width: 768px) {
        .regulasi-table { font-size: 12px; }
        .regulasi-table th, .regulasi-table td { padding: 10px 12px; }
        /* tabel 4 kolom digulir di dalam kartunya, halaman tidak melebar */
        .regulasi-group { overflow-x: auto; }
        .regulasi-search { flex-wrap: wrap; }
    }

    .btn-group-reg { display: flex; gap: 6px; flex-wrap: wrap; }
    .preview-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 12px; border-radius: 8px;
        font-size: 12px; font-weight: 600; text-decoration: none; white-space: nowrap;
        border: 1.5px solid var(--c-border-strong); color: var(--c-text); background: var(--c-surface-2);
    }
    .btn-disabled {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 12px; border-radius: 8px;
        font-size: 12px; font-weight: 600; white-space: nowrap;
        background: var(--c-surface-3); color: var(--c-text-faint); cursor: not-allowed;
    }
    .file-missing { font-size: 11.5px; color: #b45309; font-weight: 600; margin-top: 4px; }
    .search-form-btn {
        padding: 0 18px; border-radius: 12px; border: none; cursor: pointer;
        background: linear-gradient(135deg, var(--bps-dark), var(--bps-mid)); color: #FFFFFF;
        font-weight: 700; font-size: 13px;
    }
    .kelola-link {
        display: inline-flex; align-items: center; gap: 6px; padding: 0 16px; border-radius: 12px;
        border: 1.5px solid var(--c-border-strong); color: var(--c-text); background: var(--c-surface);
        font-weight: 700; font-size: 13px; text-decoration: none;
    }
    .empty-group { padding: 18px; background: var(--c-surface); color: var(--c-text-muted); font-size: 13px; border-radius: 0 0 12px 12px; }
</style>
@endpush

@section('content')
<!-- PAGE HEADER -->
<div class="page-header animate-fade-in">
    <h1 class="page-header-title">📋 Regulasi SIGMA JF</h1>
    <p class="page-header-desc">Daftar peraturan (Peraturan Kepala BPS, MenPAN-RB, Peraturan BKN), download dan preview peraturan</p>
    <div class="page-header-divider"></div>
</div>

<div class="regulasi-container">
    <!-- DISCLAIMER -->
    <div class="disclaimer-box animate-fade-in animate-delay-1">
        <span style="font-size: 18px; flex-shrink:0;">⚠️</span>
        <div>
            <strong>Catatan:</strong> Dokumen regulasi yang ditampilkan bersifat informatif. Untuk versi resmi dan terbaru,
            silakan unduh dari situs resmi BPS RI (<a href="https://www.bps.go.id" target="_blank" rel="noopener noreferrer" style="color: var(--c-heading); font-weight: 600;">www.bps.go.id</a>)
            atau JDIH BKN. Regulasi dapat berubah sewaktu-waktu mengikuti peraturan perundangan yang berlaku.
        </div>
    </div>

    <!-- SEARCH -->
    <form action="{{ route('regulasi') }}" method="GET" class="regulasi-search animate-fade-in animate-delay-1">
        <div class="search-input-wrapper">
            <span class="search-icon">🔍</span>
            <input type="text" name="q" class="search-input" id="searchInput" placeholder="Cari judul atau nomor peraturan..." value="{{ $keyword }}" oninput="filterRegulasi()">
        </div>
        <button type="submit" class="search-form-btn">Cari</button>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.regulasi.index') }}" class="kelola-link" data-action="kelola-regulasi">⚙️ Kelola Regulasi</a>
        @endif
    </form>

    <!-- FILTER BUTTONS -->
    <div class="kategori-filter animate-fade-in animate-delay-2">
        <button type="button" class="filter-btn active" onclick="filterByKat(this, 'all')">Semua</button>
        @foreach($regulasi as $kat)
        <button type="button" class="filter-btn" onclick="filterByKat(this, '{{ Str::slug($kat['kategori']) }}')">{{ $kat['kategori'] }}</button>
        @endforeach
    </div>

    <!-- REGULASI GROUPS -->
    @php $groupIcons = ['📘', '📗', '📙']; @endphp
    @foreach($regulasi as $i => $kat)
    <div class="regulasi-group animate-fade-in animate-delay-{{ $i + 2 }}" data-kategori="{{ Str::slug($kat['kategori']) }}">
        <div class="group-header">
            <div class="group-icon">{{ $groupIcons[$i % count($groupIcons)] }}</div>
            <div class="group-title">{{ $kat['kategori'] }}</div>
            <div class="group-count">{{ count($kat['items']) }} peraturan</div>
        </div>
        @if(count($kat['items']) === 0)
        <div class="empty-group">Belum ada peraturan{{ $keyword ? ' yang sesuai pencarian' : '' }}.</div>
        @else
        <table class="regulasi-table">
            <thead>
                <tr>
                    <th style="width: 220px;">Nomor Peraturan</th>
                    <th>Tentang / Perihal</th>
                    <th style="width: 90px;">Tahun</th>
                    <th style="width: 200px;">Preview / Unduh</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kat['items'] as $reg)
                <tr class="regulasi-row" data-regulasi="{{ $reg->id }}">
                    <td><div class="reg-nomor">{{ $reg->nomor }}</div></td>
                    <td><div class="reg-tentang">{{ $reg->judul }}</div></td>
                    <td><span class="tahun-badge">{{ $reg->tahun }}</span></td>
                    <td>
                        @if($reg->hasFile())
                        <div class="btn-group-reg">
                            <a href="{{ route('regulasi.preview', $reg) }}" target="_blank" rel="noopener" class="preview-btn" data-action="preview-regulasi">👁 Preview</a>
                            <a href="{{ route('regulasi.download', $reg) }}" class="download-btn" data-action="download-regulasi">⬇️ Unduh</a>
                        </div>
                        @else
                        <div class="btn-group-reg">
                            <span class="btn-disabled" aria-disabled="true">👁 Preview</span>
                            <span class="btn-disabled" aria-disabled="true">⬇️ Unduh</span>
                        </div>
                        <div class="file-missing" data-section="file-belum-tersedia">File belum tersedia</div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
function filterRegulasi() {
    const query = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('.regulasi-row');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
}

function filterByKat(btn, kat) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.regulasi-group').forEach(g => {
        g.style.display = (kat === 'all' || g.dataset.kategori === kat) ? '' : 'none';
    });
}
</script>
@endpush
