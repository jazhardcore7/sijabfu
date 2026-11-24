@extends('layouts.app')

@section('title', 'Verifikasi Berkas Usulan Pegawai')
@section('breadcrumb', 'Verifikasi Berkas Usulan Pegawai')

@include('verifikasi._styles')

@section('content')
<div class="page-header animate-fade-in vb-header">
    <h1 class="page-header-title">🔍 Verifikasi Berkas Usulan Pegawai</h1>
    <p class="page-header-desc">Daftar pegawai, berkas pegawai, pemeriksaan, perbaikan, dan keputusan MS/TMS</p>
    <div class="page-header-divider"></div>
</div>

<div class="vb-card">
    <div class="vb-card-header">
        <span>👥 Daftar Pegawai</span>
        <span class="vb-muted" data-total="{{ $daftarUsulan->total() }}">{{ $daftarUsulan->total() }} usulan</span>
    </div>

    <form action="{{ route('verifikasi.index') }}" method="GET" class="vb-filter">
        <input type="text" name="q" class="vb-input" placeholder="Cari NIP atau nama pegawai..." value="{{ request('q') }}">
        <select name="status" class="vb-select">
            <option value="">-- Semua Status --</option>
            @foreach($statusLabels as $key => $label)
            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="vb-btn">Terapkan</button>
        @if(request('q') || request('status'))
        <a href="{{ route('verifikasi.index') }}" class="vb-btn secondary">Reset</a>
        @endif
    </form>

    <table class="vb-table">
        <thead>
            <tr>
                <th>Pegawai</th>
                <th>Jabatan & Jenis Usulan</th>
                <th>Status</th>
                <th>Dikirim</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($daftarUsulan as $item)
            <tr data-usulan="{{ $item->id }}">
                <td>
                    <div style="font-weight:700;">{{ $item->nama_lengkap }}</div>
                    <div class="vb-muted">NIP {{ $item->nip }}</div>
                    <div class="vb-muted">{{ $item->satuan_kerja }}</div>
                </td>
                <td>
                    <div style="font-weight:600;">{{ $item->jabatan_fungsional }}</div>
                    <div class="vb-muted">{{ $item->jenis_usulan }}</div>
                </td>
                <td>
                    <span class="vb-badge {{ $item->statusBadgeClass() }}">{{ $item->statusKeyLabel() }}</span>
                    @if($item->hasil_verifikasi)
                    <div class="vb-muted" style="margin-top:4px;">Hasil: {{ strtoupper($item->hasil_verifikasi) }}</div>
                    @endif
                </td>
                <td class="vb-muted">{{ ($item->submitted_at ?? $item->created_at)->format('d M Y H:i') }}</td>
                <td>
                    <a href="{{ route('verifikasi.show', $item->id) }}" class="vb-btn" style="padding:7px 14px;">Periksa</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; padding:40px; color:var(--c-text-muted);">Tidak ada usulan yang sesuai filter.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="vb-pagination">{{ $daftarUsulan->links() }}</div>
</div>
@endsection
