@extends('layouts.app')

@section('title', 'Pengajuan Usulan')
@section('breadcrumb', 'Pengajuan Usulan')

@section('content')
<div class="page-header">
    <h1 class="page-header-title"><x-icon name="file-plus" /> Pengajuan Usulan</h1>
    <p class="page-header-desc">Usulan jabatan fungsional yang Anda ajukan untuk pegawai di satuan kerja Anda</p>
</div>

<div class="stack">
@if(session('success'))
<x-alert type="success">{{ session('success') }}</x-alert>
@endif
@if(session('error'))
<x-alert type="error" data-section="error">{{ session('error') }}</x-alert>
@endif

<div class="card card--flush" data-section="daftar-usulan-satker">
    <div class="card-header">
        <h2 class="card-title"><x-icon name="file-text" /> Usulan yang Anda Ajukan</h2>
        <a href="{{ route('satker.pengajuan.pilih-pegawai') }}" class="btn btn--primary btn--sm" data-action="usulan-baru"><x-icon name="file-plus" /> Usulan Baru</a>
    </div>

    <form action="{{ route('satker.pengajuan.index') }}" method="GET" class="filter-bar" role="search">
        <div class="search-input-wrapper">
            <x-icon name="search" class="search-icon" />
            <label for="usulan-q" class="sr-only">Cari NIP atau nama</label>
            <input type="search" id="usulan-q" name="q" class="search-input" placeholder="Cari NIP atau nama pegawai..." value="{{ request('q') }}">
        </div>
        <label for="usulan-status" class="sr-only">Status</label>
        <select id="usulan-status" name="status" class="filter-select">
            <option value="">-- Semua Status --</option>
            @foreach($statusLabels as $key => $label)
            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn--primary">Terapkan</button>
        @if(request('q') || request('status'))
        <a href="{{ route('satker.pengajuan.index') }}" class="btn btn--secondary">Reset</a>
        @endif
    </form>

    @if($daftarUsulan->isEmpty())
    <x-empty-state icon="file-plus" title="Belum ada usulan.">
        Pilih pegawai di satuan kerja Anda untuk membuat usulan baru.
        <x-slot:action><a href="{{ route('satker.pengajuan.pilih-pegawai') }}" class="btn btn--primary"><x-icon name="file-plus" /> Usulan Baru</a></x-slot:action>
    </x-empty-state>
    @else
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Pegawai</th>
                    <th>Satuan Kerja</th>
                    <th>Jenis Usulan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($daftarUsulan as $u)
                <tr class="usulan-item-card" data-usulan="{{ $u->id }}" data-status-key="{{ $u->status_key ?? 'draf' }}">
                    <td>
                        <div class="cell-strong">{{ $u->nama_lengkap }}</div>
                        <div class="cell-sub">NIP {{ $u->nip }}</div>
                    </td>
                    <td class="cell-sub">{{ $u->satuan_kerja }}</td>
                    <td class="cell-sub">{{ $u->jenis_usulan }}</td>
                    <td><x-status-badge :status="$u->status_key" /></td>
                    <td class="nowrap">
                        <a href="{{ route('satker.pengajuan.edit', $u) }}" class="btn btn--secondary btn--sm">
                            @if($u->isEditableByPegawai())<x-icon name="pencil" /> Ubah @else<x-icon name="eye" /> Lihat @endif
                        </a>
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
</div>
@endsection
