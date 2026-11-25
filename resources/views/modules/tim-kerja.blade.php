@extends('layouts.app')

@section('title', 'Tim Kerja')
@section('breadcrumb', 'Tim Kerja')

@section('content')
<div class="page-header">
    <h1 class="page-header-title"><x-icon name="users" /> Tim Kerja Pembinaan JF</h1>
    <p class="page-header-desc">Daftar Tim Pembinaan, profile Tim Pembinaan, dan Chat Box untuk komunikasi 2 arah</p>
</div>

<div class="tim-header-card">
    <div class="tim-header-content">
        <h2 class="tim-header-title">Tim Pembinaan JF BPS RI</h2>
        <p class="tim-header-desc">
            Tim Pembinaan JF BPS RI Pusat bertugas untuk melakukan pembinaan, pengawasan,
            dan fasilitasi pengusulan serta pengembangan seluruh Jabatan Fungsional di
            seluruh satuan kerja BPS.
        </p>
    </div>
    <div class="stat tim-stat">
        <div class="stat-value tim-stat-num" data-jumlah-anggota="{{ $tim->count() }}">{{ $tim->count() }}</div>
        <div class="stat-label tim-stat-label">Anggota Tim</div>
    </div>
</div>

@if($tim->isEmpty())
<div class="card">
    <x-empty-state icon="users" title="Belum ada anggota Tim Pembinaan JF yang terdaftar." class="tim-empty" />
</div>
@else
<ul class="tim-grid">
    @foreach($tim as $i => $anggota)
    @php
        $inisial = collect(preg_split('/\s+/', trim(preg_replace('/,.*$/', '', $anggota->name))))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    @endphp
    <li>
        <a href="{{ route('tim-kerja.show', $anggota) }}" class="tim-card" data-anggota="{{ $anggota->id }}">
            <span class="tim-avatar" aria-hidden="true">{{ $inisial }}</span>
            <span class="tim-card-body">
                <span class="tim-nama">{{ $anggota->name }}</span>
                <span class="tim-nip">NIP. {{ $anggota->nip ?? '-' }}</span>
                <span class="tim-jabatan jabatan-anggota"><x-icon name="user" /> {{ $anggota->jabatan ?? 'Tim Pembinaan JF' }}</span>
                @if($anggota->bidang)
                <span class="tim-bidang"><x-icon name="dashboard" /> {{ $anggota->bidang }}</span>
                @endif
                <span class="tim-profile-link">Lihat Profile <x-icon name="chevron-right" /></span>
            </span>
        </a>
    </li>
    @endforeach
</ul>
@endif

<section class="card contact-card">
    <div class="card-header"><h2 class="card-title"><x-icon name="send" /> Hubungi Tim Kami</h2></div>
    <dl class="contact-grid card-body">
        <div class="contact-item"><x-icon name="message" /><div><dt>Email</dt><dd>jabatanfungsional@bps.go.id</dd></div></div>
        <div class="contact-item"><x-icon name="bell" /><div><dt>Telepon</dt><dd>(021) 3841195 ext. 3xxxx</dd></div></div>
        <div class="contact-item"><x-icon name="home" /><div><dt>Alamat</dt><dd>Gedung 3 Lt. 2, BPS RI, Jakarta</dd></div></div>
        <div class="contact-item"><x-icon name="clock" /><div><dt>Jam Operasional</dt><dd>Senin–Jumat, 08.00–16.00 WIB</dd></div></div>
    </dl>
</section>
@endsection
