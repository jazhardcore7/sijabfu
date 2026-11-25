@extends('layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
@php $authUser = auth()->user(); @endphp
<div class="page-header" data-section="dashboard-admin-satker">
    <h1 class="page-header-title"><x-icon name="dashboard" /> Dashboard Admin Satuan Kerja Provinsi</h1>
    <p class="page-header-desc">
        Selamat datang, {{ $authUser->name }}.
        Cakupan: {{ $authUser->satkerLabel() }}{{ $authUser->provinsi_kode ? ' - Provinsi '.\App\Support\Satker::provinsiLabel($authUser->provinsi_kode) : '' }}.
        Ringkasan ini hanya berisi usulan yang Anda ajukan.
    </p>
</div>

<div class="stack">
@if(! $authUser->provinsi_kode)
<x-alert type="warning" data-section="provinsi-kosong">Provinsi akun Anda belum diisi. Hubungi Super Admin agar Anda dapat mengajukan usulan untuk pegawai.</x-alert>
@endif

<div class="stats-grid" data-section="ringkasan-status">
    <div class="stat stat-card" data-stat="total">
        <span class="stat-label">Total Usulan</span>
        <div class="stat-count">{{ $ringkasan['total'] }}</div>
        <span class="stat-sub">Usulan terkirim oleh Anda</span>
    </div>
    @foreach($statusLabels as $key => $label)
    <div class="stat stat-card" data-status="{{ $key }}" data-stat="{{ $key }}">
        <span class="stat-label">{{ $label }}</span>
        <div class="stat-count">{{ $ringkasan['perStatus'][$key] }}</div>
    </div>
    @endforeach
    <div class="stat stat-card" data-stat="draf">
        <span class="stat-label">Draf</span>
        <div class="stat-count">{{ $ringkasan['draf'] }}</div>
        <span class="stat-sub">Belum dikirim</span>
    </div>
</div>

@php
    // FR-SATKER-15: grafik usulan per status, hanya usulan yang diajukan Admin ini
    $chartData = [];
    $chartColors = [];
    foreach ($statusLabels as $key => $label) {
        $chartData[$label] = $ringkasan['perStatus'][$key];
        $chartColors[$label] = "var(--s-{$key}-bar)";
    }
@endphp
<div class="dash-grid" data-section="dashboard-grafik-satker">
    @include('dashboard._bar-chart', ['chartId' => 'satker-per-status', 'title' => 'Usulan Anda per Status', 'data' => $chartData, 'colors' => $chartColors])
</div>

<div class="dash-grid dash-grid--spaced">
    <div class="card card--flush" data-section="usulan-dalam-proses">
        <div class="card-header"><h2 class="card-title"><x-icon name="activity" /> Usulan dalam Proses</h2></div>
        @if($ringkasan['dalamProses']->isEmpty())
        <x-empty-state icon="file-plus" title="Belum ada usulan yang Anda ajukan." />
        @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Pegawai</th><th>Jenis Usulan</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($ringkasan['dalamProses'] as $u)
                    <tr class="usulan-item-card" data-usulan="{{ $u->id }}">
                        <td><div class="cell-strong">{{ $u->nama_lengkap }}</div><div class="cell-sub">NIP {{ $u->nip }}</div></td>
                        <td class="cell-sub">{{ $u->jenis_usulan }}</td>
                        <td>
                            <x-status-badge :status="$u->status_key" />
                            @if($u->status_key === \App\Models\FormUsulan::STATUS_PERBAIKAN || $u->isDraft())
                            <div><a href="{{ route('satker.pengajuan.edit', $u) }}" class="cell-sub" data-action="tindak-lanjut">{{ $u->isDraft() ? 'Lengkapi draf' : 'Perbaiki' }}</a></div>
                            @else
                            <div><a href="{{ route('satker.pelacakan.show', $u) }}" class="cell-sub">Lihat status</a></div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div class="dash-card" data-section="notifikasi-terbaru">
        <h2 class="dash-card-title"><x-icon name="bell" /> Notifikasi Terbaru</h2>
        @forelse($latestNotifications as $n)
        <div class="dash-notif-item">
            <a href="{{ route('notifikasi.open', $n->id) }}">{{ $n->data['judul'] ?? 'Notifikasi' }}</a>
            <div class="dash-muted">{{ $n->data['pesan'] ?? '' }}</div>
        </div>
        @empty
        <div class="dash-muted">Belum ada notifikasi.</div>
        @endforelse
    </div>
</div>
</div>
@endsection
