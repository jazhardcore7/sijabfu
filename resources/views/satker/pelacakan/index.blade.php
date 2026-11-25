@extends('layouts.app')

@section('title', 'Pelacakan Status')
@section('breadcrumb', 'Pelacakan Status')

@section('content')
<div class="page-header">
    <h1 class="page-header-title"><x-icon name="activity" /> Pelacakan Status</h1>
    <p class="page-header-desc">Pantau status usulan yang Anda ajukan, unduh surat usulan, dan lihat sertifikat UKOM pegawai</p>
</div>

<div class="card card--flush" data-section="pelacakan-satker">
    <div class="card-header">
        <h2 class="card-title"><x-icon name="activity" /> Usulan Terkirim</h2>
        <span class="group-count" data-total="{{ $daftarUsulan->total() }}">{{ $daftarUsulan->total() }} usulan</span>
    </div>

    <form action="{{ route('satker.pelacakan.index') }}" method="GET" class="filter-bar" role="search">
        <div class="search-input-wrapper">
            <x-icon name="search" class="search-icon" />
            <label for="pl-q" class="sr-only">Cari NIP atau nama</label>
            <input type="search" id="pl-q" name="q" class="search-input" placeholder="Cari NIP atau nama pegawai..." value="{{ request('q') }}">
        </div>
        <label for="pl-status" class="sr-only">Status</label>
        <select id="pl-status" name="status" class="filter-select">
            <option value="">-- Semua Status --</option>
            @foreach($statusLabels as $key => $label)
            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn--primary">Terapkan</button>
        @if(request('q') || request('status'))
        <a href="{{ route('satker.pelacakan.index') }}" class="btn btn--secondary">Reset</a>
        @endif
    </form>

    @if($daftarUsulan->isEmpty())
    <x-empty-state icon="inbox" title="Belum ada usulan terkirim.">Usulan muncul di sini setelah Anda mengirimnya dari menu Pengajuan Usulan.</x-empty-state>
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
                @foreach($daftarUsulan as $u)
                <tr class="usulan-item-card" data-usulan="{{ $u->id }}" data-status-key="{{ $u->status_key }}">
                    <td>
                        <div class="cell-strong">{{ $u->nama_lengkap }}</div>
                        <div class="cell-sub">NIP {{ $u->nip }}</div>
                        <div class="cell-sub">{{ $u->satuan_kerja }}</div>
                    </td>
                    <td>
                        <div class="cell-strong">{{ $u->jabatan_fungsional }}</div>
                        <div class="cell-sub">{{ $u->jenis_usulan }}</div>
                    </td>
                    <td>
                        <x-status-badge :status="$u->status_key" />
                        <div class="cell-sub">{{ $u->persen }}% selesai</div>
                    </td>
                    <td class="cell-sub nowrap">{{ ($u->submitted_at ?? $u->created_at)->format('d M Y') }}</td>
                    <td><a href="{{ route('satker.pelacakan.show', $u) }}" class="btn btn--secondary btn--sm"><x-icon name="eye" /> Lihat</a></td>
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
