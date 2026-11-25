@extends('layouts.app')

@section('title', 'Regulasi')
@section('breadcrumb', 'Regulasi')

@section('content')
<div class="page-header">
    <h1 class="page-header-title"><x-icon name="book" /> Regulasi SIGMA JF</h1>
    <p class="page-header-desc">Daftar peraturan (Peraturan Kepala BPS, MenPAN-RB, Peraturan BKN), download dan preview peraturan</p>
</div>

<div class="regulasi-container">
    <x-alert type="warning" class="disclaimer-box">
        <strong>Catatan:</strong> Dokumen regulasi yang ditampilkan bersifat informatif. Untuk versi resmi dan terbaru,
        silakan unduh dari situs resmi BPS RI (<a href="https://www.bps.go.id" target="_blank" rel="noopener noreferrer">www.bps.go.id</a>)
        atau JDIH BKN. Regulasi dapat berubah sewaktu-waktu mengikuti peraturan perundangan yang berlaku.
    </x-alert>

    <form action="{{ route('regulasi') }}" method="GET" class="regulasi-search" role="search">
        <div class="search-input-wrapper">
            <x-icon name="search" class="search-icon" />
            <label for="searchInput" class="sr-only">Cari judul atau nomor peraturan</label>
            <input type="search" name="q" class="search-input" id="searchInput" placeholder="Cari judul atau nomor peraturan..." value="{{ $keyword }}" oninput="filterRegulasi()">
        </div>
        <button type="submit" class="btn btn--primary">Cari</button>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.regulasi.index') }}" class="btn btn--secondary" data-action="kelola-regulasi"><x-icon name="pencil" /> Kelola Regulasi</a>
        @endif
    </form>

    <div class="kategori-filter" role="group" aria-label="Filter kategori">
        <button type="button" class="filter-btn active" aria-pressed="true" onclick="filterByKat(this, 'all')">Semua</button>
        @foreach($regulasi as $kat)
        <button type="button" class="filter-btn" aria-pressed="false" onclick="filterByKat(this, '{{ Str::slug($kat['kategori']) }}')">{{ $kat['kategori'] }}</button>
        @endforeach
    </div>

    @foreach($regulasi as $i => $kat)
    <section class="regulasi-group card card--flush" data-kategori="{{ Str::slug($kat['kategori']) }}">
        <div class="card-header">
            <h2 class="card-title"><x-icon name="book" /> {{ $kat['kategori'] }}</h2>
            <span class="group-count">{{ count($kat['items']) }} peraturan</span>
        </div>
        @if(count($kat['items']) === 0)
        <x-empty-state icon="inbox" title="Belum ada peraturan{{ $keyword ? ' yang sesuai pencarian' : '' }}." />
        @else
        <div class="table-wrap">
            <table class="table regulasi-table">
                <thead>
                    <tr>
                        <th class="col-nomor">Nomor Peraturan</th>
                        <th>Tentang / Perihal</th>
                        <th class="col-tahun">Tahun</th>
                        <th class="col-aksi">Preview / Unduh</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kat['items'] as $reg)
                    <tr class="regulasi-row" data-regulasi="{{ $reg->id }}">
                        <td class="reg-nomor">{{ $reg->nomor }}</td>
                        <td class="reg-tentang">{{ $reg->judul }}</td>
                        <td><span class="tahun-badge">{{ $reg->tahun }}</span></td>
                        <td>
                            @if($reg->hasFile())
                            <div class="btn-group-reg">
                                <a href="{{ route('regulasi.preview', $reg) }}" target="_blank" rel="noopener" class="btn btn--secondary btn--sm" data-action="preview-regulasi"><x-icon name="eye" /> Preview</a>
                                <a href="{{ route('regulasi.download', $reg) }}" class="btn btn--primary btn--sm" data-action="download-regulasi"><x-icon name="download" /> Unduh</a>
                            </div>
                            @else
                            <span class="badge-muted" data-section="file-belum-tersedia">File belum tersedia</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </section>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
function filterRegulasi() {
    const query = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('.regulasi-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
    });
}

function filterByKat(btn, kat) {
    document.querySelectorAll('.filter-btn').forEach(b => { b.classList.remove('active'); b.setAttribute('aria-pressed', 'false'); });
    btn.classList.add('active');
    btn.setAttribute('aria-pressed', 'true');

    document.querySelectorAll('.regulasi-group').forEach(g => {
        g.style.display = (kat === 'all' || g.dataset.kategori === kat) ? '' : 'none';
    });
}
</script>
@endpush
