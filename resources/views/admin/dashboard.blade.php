@extends('layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')
@section('flush', '1')

@section('content')
    {{-- Sambutan ringkas (pengganti hero besar dengan ilustrasi) --}}
    <section class="hero-section staff-hero">
        <div class="hero-inner">
            <div class="hero-text">
                <div class="hero-badge"><x-icon name="shield" /> Selamat Datang</div>
                <h1 class="hero-title">Admin Pembinaan <span>SIGMA JF</span></h1>
                <p class="hero-desc">Bersama membina, mengembangkan, dan mewujudkan ASN yang profesional.</p>
            </div>
            <div class="akses-cepat">
                <span class="akses-cepat-title"><x-icon name="bell" /> Akses Cepat:</span>
                <a href="{{ $aksesCepat['response'] }}" target="_blank" rel="noopener noreferrer" class="btn btn--secondary btn--sm">
                    RESPONSE <x-icon name="link" /><span class="sr-only">(tab baru)</span>
                </a>
            </div>
        </div>
    </section>

    {{-- GRAFIK DASHBOARD (FR-DASH-4, FR-DASH-5) --}}
    @include('dashboard._staf-panel')

    {{-- TAUTAN: INTERNAL, EKSTERNAL, LAINNYA --}}
    @php
        $linkGroups = [
            ['title' => 'Link Internal', 'icon' => 'home', 'items' => $linkInternal, 'placeholder' => '+ Tambah Link Internal (Dummy Slot)'],
            ['title' => 'Link Eksternal', 'icon' => 'link', 'items' => $linkEksternal, 'placeholder' => '+ Tambah Link Eksternal (Dummy Slot)'],
            ['title' => 'Lainnya', 'icon' => 'file-text', 'items' => $linkLainnya, 'placeholder' => '+ Tambah Link Lainnya (Dummy Slot)'],
        ];
    @endphp
    <section class="dash-wrap">
        <div class="dash-grid">
            @foreach($linkGroups as $group)
            <div class="dash-card link-card">
                <h2 class="dash-card-title"><x-icon :name="$group['icon']" /> {{ $group['title'] }}</h2>
                <ul class="link-items-list">
                    @foreach($group['items'] as $item)
                    <li>
                        <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="link-item-row" title="{{ $item['desc'] }}">
                            <span class="link-item-text">{{ $item['title'] }}</span>
                            <x-icon name="chevron-right" />
                        </a>
                    </li>
                    @endforeach
                </ul>
                <div class="slot-placeholder">{{ $group['placeholder'] }}</div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- MANAJEMEN STATUS USULAN PEGAWAI --}}
    <section class="dash-wrap usulan-section">
        @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
        @endif

        <div class="card card--flush">
            <div class="card-header">
                <h2 class="card-title"><x-icon name="file-text" /> Manajemen Status Usulan Jabatan Fungsional</h2>
                <span class="group-count">Total: {{ $daftarUsulan->count() }} Pengajuan</span>
            </div>

            @if($daftarUsulan->isEmpty())
            <x-empty-state icon="inbox" title="Belum ada berkas usulan dari pegawai">
                Usulan yang diajukan melalui portal Form Usulan akan otomatis muncul di sini.
            </x-empty-state>
            @else
            <div class="table-wrap">
                <table class="table usulan-table">
                    <thead>
                        <tr>
                            <th class="col-no">No</th>
                            <th>Pegawai &amp; Satker</th>
                            <th>Jabatan &amp; Jenis Usulan</th>
                            <th>Surat &amp; Berkas</th>
                            <th>Status Alur Saat Ini</th>
                            <th>Kelola Usulan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($daftarUsulan as $index => $item)
                        <tr>
                            <td class="cell-muted">{{ $index + 1 }}</td>
                            <td>
                                <div class="cell-strong">{{ $item->nama_lengkap }}</div>
                                <div class="cell-sub">NIP: {{ $item->nip }}</div>
                                <div class="cell-sub">{{ $item->satuan_kerja }}</div>
                                <div class="cell-sub">{{ $item->email }}</div>
                            </td>
                            <td>
                                <div class="cell-strong">{{ $item->jabatan_fungsional }}</div>
                                <div class="cell-sub">{{ $item->jenis_usulan }}</div>
                                <div class="cell-sub">Diajukan: {{ $item->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td>
                                <div class="cell-strong">No: {{ $item->nomor_surat }}</div>
                                <div class="cell-sub cell-clip" title="{{ $item->hal_surat }}">Hal: {{ $item->hal_surat }}</div>
                                @php
                                    $safeBerkas = \App\Models\FormUsulan::safeUrl($item->link_berkas);
                                    $safeUsulan = \App\Models\FormUsulan::safeUrl($item->link_usulan);
                                @endphp
                                @if($safeBerkas)
                                <a href="{{ $safeBerkas }}" target="_blank" rel="noopener noreferrer" class="cell-link"><x-icon name="link" /> Buka Berkas (Drive/Laci) ↗</a>
                                @endif
                                @if($safeUsulan)
                                <a href="{{ $safeUsulan }}" target="_blank" rel="noopener noreferrer" class="cell-link">Link Usulan ↗</a>
                                @endif
                            </td>
                            <td>
                                <span class="status-badge" data-status="{{ $item->status_key ?: 'draf' }}" data-status-key="{{ $item->status_key }}">{{ $item->statusKeyLabel() }}</span>
                                @if($item->hasil_verifikasi)
                                <div class="cell-sub">Hasil verifikasi: <strong>{{ strtoupper($item->hasil_verifikasi) }}</strong></div>
                                @endif
                                @if($item->catatan)
                                <div class="cell-note"><strong>Catatan:</strong> {{ $item->catatan }}</div>
                                @endif
                            </td>
                            <td>
                                {{-- Perubahan status hanya lewat state machine (FR-MON-2) --}}
                                <div class="cell-actions">
                                    <a href="{{ route('verifikasi.show', $item->id) }}" class="btn btn--primary btn--sm" data-action="buka-verifikasi"><x-icon name="search-check" /> Verifikasi</a>
                                    <a href="{{ route('monitoring.index', ['q' => $item->nip]) }}" class="btn btn--secondary btn--sm" data-action="buka-monitoring"><x-icon name="pencil" /> Kelola</a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </section>
@endsection
