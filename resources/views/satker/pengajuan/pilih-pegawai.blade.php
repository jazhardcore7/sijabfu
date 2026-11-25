@extends('layouts.app')

@section('title', 'Pilih Pegawai')
@section('breadcrumb', 'Pengajuan Usulan')

@section('content')
<div class="page-header">
    <h1 class="page-header-title"><x-icon name="users" /> Pilih Pegawai</h1>
    <p class="page-header-desc">
        Pegawai Provinsi dan Kabupaten/Kota di Provinsi {{ \App\Support\Satker::provinsiLabel($admin->provinsi_kode) }}.
        Setiap pegawai hanya dapat memiliki satu usulan aktif.
    </p>
</div>

<div class="stack">
@if(session('error'))
<x-alert type="error" data-section="error">{{ session('error') }}</x-alert>
@endif
@if(session('warning'))
<x-alert type="warning" data-section="warning">{{ session('warning') }}</x-alert>
@endif
@if(! $admin->provinsi_kode)
<x-alert type="warning" data-section="provinsi-kosong">Provinsi akun Anda belum diisi. Hubungi Super Admin.</x-alert>
@endif

<div class="card card--flush" data-section="pilih-pegawai">
    <div class="card-header">
        <h2 class="card-title"><x-icon name="users" /> Pegawai</h2>
        <span class="group-count" data-total="{{ $pegawaiList->total() }}">{{ $pegawaiList->total() }} pegawai</span>
    </div>

    <form action="{{ route('satker.pengajuan.pilih-pegawai') }}" method="GET" class="filter-bar" role="search">
        <div class="search-input-wrapper">
            <x-icon name="search" class="search-icon" />
            <label for="pegawai-q" class="sr-only">Cari NIP atau nama</label>
            <input type="search" id="pegawai-q" name="q" class="search-input" placeholder="Cari NIP atau nama..." value="{{ request('q') }}">
        </div>
        <button type="submit" class="btn btn--primary">Cari</button>
        @if(request('q'))
        <a href="{{ route('satker.pengajuan.pilih-pegawai') }}" class="btn btn--secondary">Reset</a>
        @endif
        <a href="{{ route('satker.pengajuan.index') }}" class="btn btn--ghost"><x-icon name="arrow-left" /> Kembali</a>
    </form>

    @if($pegawaiList->isEmpty())
    <x-empty-state icon="users" title="Tidak ada pegawai dalam cakupan Anda.">Pegawai muncul di sini bila satuan kerjanya Provinsi atau Kabupaten/Kota di provinsi Anda.</x-empty-state>
    @else
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama &amp; NIP</th>
                    <th>Jabatan</th>
                    <th>Satuan Kerja</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pegawaiList as $p)
                <tr data-pegawai="{{ $p->id }}">
                    <td>
                        <div class="cell-strong">{{ $p->name }}</div>
                        <div class="cell-sub">NIP {{ $p->nip ?? '-' }}</div>
                    </td>
                    <td class="cell-sub">{{ $p->jabatan ?? '-' }}</td>
                    <td class="cell-sub">{{ $p->unit_kerja ?? '-' }} ({{ $p->satkerLabel() }}{{ $p->satker_kode ? ' '.$p->satker_kode : '' }})</td>
                    <td class="nowrap">
                        @if($p->usulan_aktif)
                        <span class="badge-muted" data-usulan-aktif="{{ $p->usulan_aktif->id }}">Sudah ada usulan aktif</span>
                        @else
                        <a href="{{ route('satker.pengajuan.create', $p) }}" class="btn btn--primary btn--sm" data-action="ajukan"><x-icon name="file-plus" /> Ajukan</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($pegawaiList->hasPages())
    <div class="card-footer pagination-wrap">{{ $pegawaiList->links() }}</div>
    @endif
</div>
</div>
@endsection
