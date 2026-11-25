@extends('layouts.app')

@section('title', 'Verifikasi Berkas Usulan Pegawai')
@section('breadcrumb', 'Verifikasi Berkas Usulan Pegawai')

@section('content')
<div class="page-header">
    <h1 class="page-header-title"><x-icon name="search-check" /> Verifikasi Berkas Usulan Pegawai</h1>
    <p class="page-header-desc">Daftar pegawai, berkas pegawai, pemeriksaan, perbaikan, dan keputusan MS/TMS</p>
</div>

<div class="card card--flush">
    <div class="card-header">
        <h2 class="card-title"><x-icon name="users" /> Daftar Pegawai</h2>
        <span class="group-count" data-total="{{ $daftarUsulan->total() }}">{{ $daftarUsulan->total() }} usulan</span>
    </div>

    <form action="{{ route('verifikasi.index') }}" method="GET" class="filter-bar" role="search">
        <div class="search-input-wrapper">
            <x-icon name="search" class="search-icon" />
            <label for="vb-q" class="sr-only">Cari NIP atau nama pegawai</label>
            <input type="search" id="vb-q" name="q" class="search-input" placeholder="Cari NIP atau nama pegawai..." value="{{ request('q') }}">
        </div>
        <label for="vb-status" class="sr-only">Status</label>
        <select id="vb-status" name="status" class="filter-select">
            <option value="">-- Semua Status --</option>
            @foreach($statusLabels as $key => $label)
            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn--primary">Terapkan</button>
        @if(request('q') || request('status'))
        <a href="{{ route('verifikasi.index') }}" class="btn btn--secondary">Reset</a>
        @endif
    </form>

    @if($daftarUsulan->isEmpty())
    <x-empty-state icon="inbox" title="Tidak ada usulan yang sesuai filter." />
    @else
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Pegawai</th>
                    <th>Jabatan &amp; Jenis Usulan</th>
                    <th>Status</th>
                    <th>Dikirim</th>
                    <th><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach($daftarUsulan as $item)
                <tr data-usulan="{{ $item->id }}">
                    <td>
                        <div class="cell-strong">{{ $item->nama_lengkap }}</div>
                        <div class="cell-sub">NIP {{ $item->nip }}</div>
                        <div class="cell-sub">{{ $item->satuan_kerja }}</div>
                    </td>
                    <td>
                        <div class="cell-strong">{{ $item->jabatan_fungsional }}</div>
                        <div class="cell-sub">{{ $item->jenis_usulan }}</div>
                    </td>
                    <td>
                        <x-status-badge :status="$item->status_key" :label="$item->statusKeyLabel()" />
                        @if($item->hasil_verifikasi)
                        <div class="cell-sub">Hasil: {{ strtoupper($item->hasil_verifikasi) }}</div>
                        @endif
                    </td>
                    <td class="cell-sub nowrap">{{ ($item->submitted_at ?? $item->created_at)->format('d M Y H:i') }}</td>
                    <td>
                        <a href="{{ route('verifikasi.show', $item->id) }}" class="btn btn--primary btn--sm">Periksa</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($daftarUsulan->hasPages())
    <div class="card-footer pagination-wrap">{{ $daftarUsulan->links() }}</div>
    @endif
</div>
@endsection
