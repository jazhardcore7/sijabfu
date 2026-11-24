@extends('layouts.app')

@section('title', 'Sinkronisasi Data SSO')
@section('breadcrumb', 'Sinkronisasi Data SSO')

@include('verifikasi._styles')

@section('content')
<div class="page-header animate-fade-in vb-header">
    <h1 class="page-header-title">🔄 Sinkronisasi Data SSO</h1>
    <p class="page-header-desc">Perbarui data user (nama, email, jabatan, unit kerja) dari direktori SSO berdasarkan NIP. Role user tidak diubah.</p>
    <div class="page-header-divider"></div>
</div>

@if(session('success'))
<div class="vb-alert success" data-section="sync-success"><span>✅</span><span>{{ session('success') }}</span></div>
@endif
@if(session('error'))
<div class="vb-alert error" data-section="sync-error"><span>⚠️</span><span>{{ session('error') }}</span></div>
@endif

<div class="vb-card">
    <div class="vb-card-header">
        <span>⚙️ Jalankan Sinkronisasi</span>
        <span class="vb-muted">Driver: {{ $driver }}</span>
    </div>
    <div class="vb-card-body">
        <form action="{{ route('admin.sso-sync.run') }}" method="POST" onsubmit="return confirm('Jalankan sinkronisasi data SSO sekarang?');">
            @csrf
            <button type="submit" class="vb-btn" data-action="sinkronkan">🔄 Sinkronkan</button>
        </form>
    </div>
</div>

<div class="vb-card">
    <div class="vb-card-header"><span>🕒 Riwayat Sinkronisasi</span></div>
    <table class="vb-table">
        <thead>
            <tr>
                <th>Mulai</th>
                <th>Selesai</th>
                <th>Baru</th>
                <th>Diperbarui</th>
                <th>Gagal</th>
                <th>Oleh</th>
                <th>Error</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr data-log="{{ $log->id }}">
                <td>{{ $log->started_at?->format('d M Y H:i') ?? '-' }}</td>
                <td>{{ $log->finished_at?->format('d M Y H:i') ?? 'Belum selesai' }}</td>
                <td>{{ $log->created_count }}</td>
                <td>{{ $log->updated_count }}</td>
                <td>{{ $log->failed_count }}</td>
                <td class="vb-muted">{{ $log->triggeredBy?->name ?? '-' }}</td>
                <td class="vb-muted" style="max-width:360px; word-break:break-word;">{{ $log->error ?? '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center; padding:30px; color:var(--c-text-muted);">Belum pernah sinkronisasi.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="vb-pagination">{{ $logs->links() }}</div>
</div>
@endsection
