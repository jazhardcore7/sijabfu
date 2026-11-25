@extends('layouts.app')

@section('title', 'Monitoring Progress UKOM')
@section('breadcrumb', 'Monitoring Progress UKOM')

@section('content')
<div class="page-header">
    <h1 class="page-header-title"><x-icon name="bar-chart" /> Monitoring Progress UKOM</h1>
    <p class="page-header-desc">
        Pantau progres setiap pegawai, terbitkan surat usulan, dan unggah sertifikat hasil UKOM.
        Perubahan status mengikuti alur Verifikasi → Disetujui → Ditetapkan → Selesai.
    </p>
</div>

{{-- STATISTIK PER STATUS (FR-MON-1): setiap kartu juga filter --}}
<nav class="stats-grid" aria-label="Filter status">
    <a href="{{ route('monitoring.index', array_filter(['q' => request('q'), 'satker' => request('satker'), 'provinsi' => request('provinsi')])) }}" class="stat stat-card {{ !request('status') ? 'active-filter' : '' }}" data-stat="total" @if(!request('status')) aria-current="true" @endif>
        <span class="stat-label">Total Usulan</span>
        <div class="stat-count">{{ $stats['total'] }}</div>
        <span class="stat-sub">Seluruh usulan terkirim</span>
    </a>
    @foreach($statusLabels as $key => $label)
    <a href="{{ route('monitoring.index', array_filter(['status' => $key, 'q' => request('q'), 'satker' => request('satker'), 'provinsi' => request('provinsi')])) }}" class="stat stat-card {{ request('status') === $key ? 'active-filter' : '' }}" data-stat="{{ $key }}" data-status="{{ $key }}" @if(request('status') === $key) aria-current="true" @endif>
        <span class="stat-label"><span class="stat-dot" aria-hidden="true"></span>{{ $label }}</span>
        <div class="stat-count" data-count="{{ $stats[$key] }}">{{ $stats[$key] }}</div>
        <span class="stat-sub">Usulan</span>
    </a>
    @endforeach
</nav>

<div class="stack">
    @if(session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
    @endif

    @if($transitionError ?? session('error'))
    <x-alert type="error" data-section="transition-error">{{ $transitionError ?? session('error') }}</x-alert>
    @endif

    @if($errors->any())
    <x-alert type="error" data-section="validation-errors">{{ implode(' ', $errors->all()) }}</x-alert>
    @endif

    {{-- FILTER & PENCARIAN --}}
    <form action="{{ route('monitoring.index') }}" method="GET" class="card filter-bar filter-bar--card" role="search">
        <div class="search-input-wrapper">
            <x-icon name="search" class="search-icon" />
            <label for="mon-q" class="sr-only">Cari NIP atau nama pegawai</label>
            <input type="search" id="mon-q" name="q" class="search-input" placeholder="Cari NIP atau nama pegawai..." value="{{ request('q') }}">
        </div>
        <label for="mon-status" class="sr-only">Status</label>
        <select id="mon-status" name="status" class="filter-select">
            <option value="">-- Semua Status --</option>
            @foreach($statusLabels as $key => $label)
            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @include('partials.satker-filter', ['prefix' => 'mon'])
        <button type="submit" class="btn btn--primary">Terapkan Filter</button>
        @if(request('q') || request('status') || request('satker') || request('provinsi'))
        <a href="{{ route('monitoring.index') }}" class="btn btn--secondary">Reset Filter</a>
        @endif
    </form>

    {{-- DAFTAR PROGRES PEGAWAI --}}
    @php $order = ['verifikasi' => 1, 'perbaikan' => 1, 'disetujui' => 2, 'ditetapkan' => 3, 'selesai' => 4]; @endphp
    @forelse($daftarUsulan as $item)
    @php
        $rank = $order[$item->status_key] ?? 1;
        $tahap = ['verifikasi' => 'Verifikasi', 'disetujui' => 'Disetujui', 'ditetapkan' => 'Ditetapkan', 'selesai' => 'Selesai'];
        $persen = $item->status_key === 'selesai' ? 100 : (int) round((($rank - 1) / 4) * 100);
        $formQuery = array_filter(['status' => request('status'), 'q' => request('q'), 'satker' => request('satker'), 'provinsi' => request('provinsi')]);
        $steps = [];
        foreach (array_values($tahap) as $i => $label) {
            $s = $i + 1;
            $isDone = $item->status_key === 'selesai' || $rank > $s;
            $isActive = !$isDone && $rank === $s;
            $steps[] = [
                'key' => array_keys($tahap)[$i],
                'label' => $label,
                'state' => $isDone ? 'done' : ($isActive ? ($item->status_key === 'perbaikan' ? 'perbaikan' : 'active') : 'pending'),
            ];
        }
    @endphp
    <article class="usulan-item-card" data-usulan="{{ $item->id }}">
        <header class="mon-card-top">
            <div class="mon-card-who">
                <span class="tim-avatar" aria-hidden="true">{{ strtoupper(mb_substr($item->nama_lengkap, 0, 2)) }}</span>
                <div class="min-w-0">
                    <h2 class="mon-name">{{ $item->nama_lengkap }}</h2>
                    <div class="cell-sub"><strong>NIP:</strong> {{ $item->nip }} · {{ $item->satuan_kerja }}</div>
                    <div class="cell-sub" data-diajukan-oleh="{{ $item->isSelfSubmitted() ? 'mandiri' : 'admin_satker' }}"><strong>Diajukan oleh:</strong> {{ $item->diajukanOlehLabel() }}</div>
                </div>
            </div>
            <div class="mon-card-badges">
                <span class="status-badge" data-status="{{ $item->status_key ?: 'draf' }}" data-status-key="{{ $item->status_key }}">{{ $item->statusKeyLabel() }} ({{ $persen }}%)</span>
                @if($safeBerkas = \App\Models\FormUsulan::safeUrl($item->link_berkas))
                <a href="{{ $safeBerkas }}" target="_blank" rel="noopener noreferrer" class="btn btn--ghost btn--sm"><x-icon name="link" /> Berkas Laci/Drive ↗</a>
                @endif
            </div>
        </header>

        <div class="mon-card-body">
            <div class="min-w-0">
                <dl class="dossier-grid">
                    <div><dt>Jabatan Fungsional</dt><dd>{{ $item->jabatan_fungsional }}</dd></div>
                    <div><dt>Jenis Usulan</dt><dd>{{ $item->jenis_usulan }}</dd></div>
                    <div><dt>Hasil Verifikasi</dt><dd>{{ $item->hasil_verifikasi ? strtoupper($item->hasil_verifikasi) : '-' }}</dd></div>
                    <div class="dossier-wide"><dt>Nomor / Hal Surat</dt><dd>{{ $item->nomor_surat }} — {{ $item->hal_surat }}</dd></div>
                    <div><dt>Dikirim</dt><dd>{{ ($item->submitted_at ?? $item->created_at)->format('d M Y, H:i') }}</dd></div>
                </dl>

                <x-tracker class="mon-tracker" :steps="$steps" />

                @if($item->catatan)
                <div class="cell-note"><strong>Catatan terbaru:</strong> {{ $item->catatan }}</div>
                @endif

                @if($item->surat_usulan_path || $item->sertifikat_path || $item->hasil_ukom)
                <div class="mon-doc-links">
                    @if($item->surat_usulan_path)
                    <a class="btn btn--secondary btn--sm" href="{{ route('files.show', ['type' => 'surat', 'id' => $item->id, 'mode' => 'preview']) }}" target="_blank" rel="noopener"><x-icon name="file-text" /> Surat Usulan</a>
                    @endif
                    @if($item->sertifikat_path)
                    <a class="btn btn--secondary btn--sm" href="{{ route('files.show', ['type' => 'sertifikat', 'id' => $item->id, 'mode' => 'preview']) }}" target="_blank" rel="noopener"><x-icon name="shield" /> Sertifikat UKOM</a>
                    @endif
                    @if($item->hasil_ukom)
                    <span class="cell-sub">Hasil UKOM: <strong>{{ $item->hasil_ukom }}</strong></span>
                    @endif
                </div>
                @endif
            </div>

            {{-- PANEL UPDATE STATUS (FR-MON-2, FR-MON-4, FR-MON-5) --}}
            <div class="control-panel-box">
                <h3 class="control-panel-title"><x-icon name="pencil" /> Update Status</h3>

                @if(in_array($item->status_key, ['verifikasi', 'perbaikan'], true))
                <a href="{{ route('verifikasi.show', $item->id) }}" class="btn btn--primary btn--block" data-action="ke-verifikasi"><x-icon name="search-check" /> Verifikasi Berkas</a>
                <p class="mon-hint">
                    {{ $item->status_key === 'perbaikan' ? 'Menunggu pegawai memperbaiki dan mengirim ulang berkas.' : 'Keputusan MS/TMS dilakukan di halaman Verifikasi Berkas.' }}
                </p>
                @elseif($item->status_key === 'disetujui')
                <form action="{{ route('monitoring.update-status', ['usulan' => $item->id] + $formQuery) }}" method="POST" enctype="multipart/form-data" class="mon-action-form" data-form="tetapkan">
                    @csrf
                    <input type="hidden" name="aksi" value="tetapkan">
                    <label class="field-label" for="surat-{{ $item->id }}">Upload Surat Usulan (PDF):</label>
                    <input type="file" id="surat-{{ $item->id }}" name="surat" accept="application/pdf,.pdf" class="slot-file" required>
                    <label class="sr-only" for="catatan-tetapkan-{{ $item->id }}">Catatan untuk pegawai (opsional)</label>
                    <input type="text" id="catatan-tetapkan-{{ $item->id }}" name="catatan" class="vb-input" placeholder="Catatan untuk pegawai (opsional)">
                    <button type="submit" class="btn btn--primary btn--block"><x-icon name="file-text" /> Tetapkan</button>
                </form>
                @elseif($item->status_key === 'ditetapkan')
                <form action="{{ route('monitoring.update-status', ['usulan' => $item->id] + $formQuery) }}" method="POST" enctype="multipart/form-data" class="mon-action-form" data-form="selesaikan">
                    @csrf
                    <input type="hidden" name="aksi" value="selesaikan">
                    <label class="field-label" for="sertifikat-{{ $item->id }}">Upload Sertifikat UKOM (PDF):</label>
                    <input type="file" id="sertifikat-{{ $item->id }}" name="sertifikat" accept="application/pdf,.pdf" class="slot-file" required>
                    <label class="sr-only" for="hasil-ukom-{{ $item->id }}">Hasil UKOM (opsional)</label>
                    <input type="text" id="hasil-ukom-{{ $item->id }}" name="hasil_ukom" class="vb-input" placeholder="Hasil UKOM (opsional)">
                    <label class="sr-only" for="catatan-selesai-{{ $item->id }}">Catatan untuk pegawai (opsional)</label>
                    <input type="text" id="catatan-selesai-{{ $item->id }}" name="catatan" class="vb-input" placeholder="Catatan untuk pegawai (opsional)">
                    <button type="submit" class="btn btn--primary btn--block"><x-icon name="check-circle" /> Selesai</button>
                </form>
                @else
                <p class="mon-hint">Usulan telah selesai.</p>
                @endif

                @if($isSuperAdmin && ($order[$item->status_key] ?? 1) > 1)
                <button type="button" class="btn btn--secondary btn--block mon-rollback-open" data-modal-open="rollback-{{ $item->id }}"><x-icon name="refresh" /> Rollback</button>
                <x-modal id="rollback-{{ $item->id }}" title="Rollback status (Super Admin):">
                    <form action="{{ route('monitoring.update-status', ['usulan' => $item->id] + $formQuery) }}" method="POST" class="mon-action-form" data-form="rollback" id="form-rollback-{{ $item->id }}">
                        @csrf
                        <input type="hidden" name="aksi" value="rollback">
                        <div class="cell-strong">{{ $item->nama_lengkap }}</div>
                        <label class="sr-only" for="ke-status-{{ $item->id }}">Status</label>
                        <select id="ke-status-{{ $item->id }}" name="ke_status" class="filter-select" required>
                            @foreach($statusLabels as $key => $label)
                                @if(($order[$key] ?? 1) < ($order[$item->status_key] ?? 1))
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                        <label class="sr-only" for="alasan-{{ $item->id }}">Alasan rollback (wajib)</label>
                        <input type="text" id="alasan-{{ $item->id }}" name="alasan" class="vb-input" placeholder="Alasan rollback (wajib)" required>
                    </form>
                    <x-slot:footer>
                        <button type="button" class="btn btn--secondary" data-modal-close>Batal</button>
                        <button type="submit" form="form-rollback-{{ $item->id }}" class="btn btn--danger"><x-icon name="refresh" /> Rollback</button>
                    </x-slot:footer>
                </x-modal>
                @endif
            </div>
        </div>
    </article>
    @empty
    <div class="card">
        <x-empty-state icon="inbox" title="Tidak ada usulan ditemukan">
            Sesuaikan filter pencarian atau tunggu usulan baru dari pegawai.
        </x-empty-state>
    </div>
    @endforelse

    @if($daftarUsulan->hasPages())
    <div class="pagination-wrap">{{ $daftarUsulan->links() }}</div>
    @endif
</div>

<footer class="page-footer">
    Copyright &copy; 2026 Badan Pusat Statistik RI — Tim Pembinaan JF (SIGMA JF)
</footer>
@endsection
