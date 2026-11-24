@extends('layouts.app')

@section('title', 'Profile Tim Pembinaan')
@section('breadcrumb', 'Tim Kerja')

@push('styles')
<style>
    .profile-card {
        max-width: 760px; background: var(--c-surface); border: 1px solid var(--c-border);
        border-radius: 20px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.05);
    }
    .profile-top { background: linear-gradient(135deg, var(--bps-dark), var(--bps-mid)); padding: 28px; display: flex; gap: 20px; align-items: center; color: #FFFFFF; }
    .profile-avatar {
        width: 88px; height: 88px; border-radius: 50%; flex-shrink: 0;
        background: rgba(255,255,255,0.18); border: 3px solid rgba(255,255,255,0.4);
        display: flex; align-items: center; justify-content: center; font-size: 30px; font-weight: 900;
    }
    .profile-name { font-size: 21px; font-weight: 800; }
    .profile-sub { font-size: 13px; opacity: 0.85; margin-top: 4px; }
    .profile-grid { display: grid; grid-template-columns: 1fr 1fr; }
    .profile-grid > div { padding: 14px 24px; border-bottom: 1px solid var(--c-border-soft); }
    .profile-label { font-size: 11px; color: var(--c-text-faint); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
    .profile-value { font-size: 14px; font-weight: 600; color: var(--c-text); word-break: break-word; }
    .profile-actions { padding: 18px 24px; display: flex; gap: 10px; flex-wrap: wrap; }
    .profile-btn {
        display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; border-radius: 10px;
        font-size: 13px; font-weight: 700; text-decoration: none;
        background: linear-gradient(135deg, var(--bps-dark), var(--bps-mid)); color: #FFFFFF;
    }
    .profile-btn.secondary { background: var(--c-surface-2); color: var(--c-text); border: 1.5px solid var(--c-border-strong); }
    @media (max-width: 640px) { .profile-grid { grid-template-columns: 1fr; } .profile-top { flex-direction: column; text-align: center; } }
</style>
@endpush

@section('content')
@php
    $inisial = collect(preg_split('/\s+/', trim(preg_replace('/,.*$/', '', $anggota->name))))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
@endphp

<div class="page-header animate-fade-in">
    <h1 class="page-header-title">👤 Profile Tim Pembinaan</h1>
    <p class="page-header-desc"><a href="{{ route('tim-kerja') }}" style="color:var(--bps-mid); font-weight:600; text-decoration:none;">← Daftar Tim Pembinaan</a></p>
    <div class="page-header-divider"></div>
</div>

<div class="profile-card animate-fade-in" data-profile="{{ $anggota->id }}">
    <div class="profile-top">
        {{-- Foto: inisial sampai fitur unggah foto tersedia (Fase 8) --}}
        <div class="profile-avatar" data-section="foto">{{ $inisial }}</div>
        <div>
            <div class="profile-name">{{ $anggota->name }}</div>
            <div class="profile-sub">{{ $anggota->jabatan ?? 'Tim Pembinaan JF' }}</div>
        </div>
    </div>
    <div class="profile-grid">
        <div><div class="profile-label">Nama</div><div class="profile-value">{{ $anggota->name }}</div></div>
        <div><div class="profile-label">NIP</div><div class="profile-value">{{ $anggota->nip ?? '-' }}</div></div>
        <div><div class="profile-label">Jabatan</div><div class="profile-value">{{ $anggota->jabatan ?? '-' }}</div></div>
        <div><div class="profile-label">Bidang</div><div class="profile-value">{{ $anggota->bidang ?? '-' }}</div></div>
        <div style="grid-column: 1 / -1;"><div class="profile-label">Kontak</div><div class="profile-value">{{ $anggota->kontak ?? $anggota->email }}</div></div>
    </div>
    <div class="profile-actions">
        @if(auth()->user()->isPegawai())
        <a href="{{ route('chat.index') }}" class="profile-btn" data-action="chat-tim">💬 Chat dengan Tim Pembinaan</a>
        @endif
        <a href="{{ route('tim-kerja') }}" class="profile-btn secondary">← Kembali</a>
    </div>
</div>
@endsection
