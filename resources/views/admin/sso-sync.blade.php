@extends('layouts.app')

@section('title', 'Sinkronisasi Data SSO')
@section('breadcrumb', 'Sinkronisasi Data SSO')

@section('content')
<div class="page-header">
    <h1 class="page-header-title"><x-icon name="refresh" /> Sinkronisasi Data SSO</h1>
    <p class="page-header-desc">Perbarui data user (nama, email, jabatan, unit kerja, satuan kerja) dari direktori SSO berdasarkan NIP. Role user tidak diubah.</p>
</div>

<div class="stack">
@if(session('success'))
<x-alert type="success" data-section="sync-success">{{ session('success') }}</x-alert>
@endif
@if(session('error'))
<x-alert type="error" data-section="sync-error">{{ session('error') }}</x-alert>
@endif

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><x-icon name="refresh" /> Jalankan Sinkronisasi</h2>
        <span class="badge-muted">Driver: {{ $driver }}</span>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.sso-sync.run') }}" method="POST" id="form-sso-sync">
            @csrf
            <button type="button" class="btn btn--primary" data-action="sinkronkan" data-modal-open="modal-sso-sync"><x-icon name="refresh" /> Sinkronkan</button>
        </form>
        <x-modal id="modal-sso-sync" title="Sinkronisasi Data SSO">
            Jalankan sinkronisasi data SSO sekarang?
            <x-slot:footer>
                <button type="button" class="btn btn--secondary" data-modal-close>Batal</button>
                <button type="submit" form="form-sso-sync" class="btn btn--primary">Sinkronkan</button>
            </x-slot:footer>
        </x-modal>
    </div>
</div>

<div class="card card--flush">
    <div class="card-header"><h2 class="card-title"><x-icon name="clock" /> Riwayat Sinkronisasi</h2></div>
    @if($logs->isEmpty())
    <x-empty-state icon="clock" title="Belum pernah sinkronisasi." />
    @else
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Mulai</th>
                    <th>Selesai</th>
                    <th class="num">Baru</th>
                    <th class="num">Diperbarui</th>
                    <th class="num">Gagal</th>
                    <th class="num">Tanpa Satker</th>
                    <th>Oleh</th>
                    <th>Error</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr data-log="{{ $log->id }}">
                    <td class="nowrap">{{ $log->started_at?->format('d M Y H:i') ?? '-' }}</td>
                    <td class="nowrap">{{ $log->finished_at?->format('d M Y H:i') ?? 'Belum selesai' }}</td>
                    <td class="num">{{ $log->created_count }}</td>
                    <td class="num">{{ $log->updated_count }}</td>
                    <td class="num">@if($log->failed_count > 0)<span class="text-danger"><x-icon name="alert-circle" /> {{ $log->failed_count }}</span>@else{{ $log->failed_count }}@endif</td>
                    <td class="num" data-satker-missing="{{ $log->satker_missing_count }}">@if($log->satker_missing_count > 0)<a href="{{ route('admin.users', ['satker' => 'kosong']) }}">{{ $log->satker_missing_count }}</a>@else{{ $log->satker_missing_count }}@endif</td>
                    <td class="cell-sub">{{ $log->triggeredBy?->name ?? '-' }}</td>
                    <td class="cell-sub cell-wrap">{{ $log->error ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    @if($logs->hasPages())
    <div class="card-footer pagination-wrap">{{ $logs->links() }}</div>
    @endif
</div>
</div>
@endsection
