@extends('layouts.app')

@section('title', 'Profile Tim Pembinaan')
@section('breadcrumb', 'Tim Kerja')

@section('content')
@php
    $inisial = collect(preg_split('/\s+/', trim(preg_replace('/,.*$/', '', $anggota->name))))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
@endphp

<div class="page-header">
    <h1 class="page-header-title"><x-icon name="user" /> Profile Tim Pembinaan</h1>
    <p class="page-header-desc"><a href="{{ route('tim-kerja') }}" class="link-back"><x-icon name="arrow-left" /> Daftar Tim Pembinaan</a></p>
</div>

<div class="card tp-card" data-profile="{{ $anggota->id }}">
    <div class="tp-top">
        {{-- Foto: inisial sampai fitur unggah foto tersedia --}}
        <div class="tim-avatar tp-avatar" data-section="foto">{{ $inisial }}</div>
        <div>
            <h2 class="tp-name">{{ $anggota->name }}</h2>
            <div class="tp-sub">{{ $anggota->jabatan ?? 'Tim Pembinaan JF' }}</div>
        </div>
    </div>
    <dl class="tp-grid">
        <div><dt>Nama</dt><dd>{{ $anggota->name }}</dd></div>
        <div><dt>NIP</dt><dd>{{ $anggota->nip ?? '-' }}</dd></div>
        <div><dt>Jabatan</dt><dd>{{ $anggota->jabatan ?? '-' }}</dd></div>
        <div><dt>Bidang</dt><dd>{{ $anggota->bidang ?? '-' }}</dd></div>
        <div class="tp-full"><dt>Kontak</dt><dd>{{ $anggota->kontak ?? $anggota->email }}</dd></div>
    </dl>
    <div class="card-footer tp-actions">
        @if(auth()->user()->isPegawai())
        <a href="{{ route('chat.index') }}" class="btn btn--primary" data-action="chat-tim"><x-icon name="message" /> Chat dengan Tim Pembinaan</a>
        @endif
        <a href="{{ route('tim-kerja') }}" class="btn btn--secondary"><x-icon name="arrow-left" /> Kembali</a>
    </div>
</div>
@endsection
