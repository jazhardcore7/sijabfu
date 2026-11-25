@extends('layouts.app')

@section('title', 'Alur Layanan')
@section('breadcrumb', 'Alur Layanan')

@section('content')
<div class="page-header">
    <h1 class="page-header-title"><x-icon name="route" /> Alur Layanan SIGMA JF</h1>
    <p class="page-header-desc">Panduan lengkap proses pengusulan jabatan fungsional di lingkungan BPS Republik Indonesia</p>
</div>

<section class="card timeline-summary">
    <div class="card-header"><h2 class="card-title"><x-icon name="activity" /> Ringkasan Alur Proses</h2></div>
    <ol class="timeline-flow card-body">
        <li class="flow-step"><x-icon name="search-check" /> Verifikasi</li>
        <li class="flow-step"><x-icon name="file-text" /> Surat Usulan Ukom</li>
        <li class="flow-step"><x-icon name="users" /> Pengelola</li>
        <li class="flow-step"><x-icon name="shield" /> Ukom</li>
        <li class="flow-step"><x-icon name="home" /> Instansi Pembina</li>
        <li class="flow-step"><x-icon name="bell" /> Pengumuman</li>
    </ol>
</section>

<ol class="steps-container">
    @foreach($steps as $index => $step)
    <li class="step-item">
        <span class="step-number-circle" aria-hidden="true">{{ $step['number'] }}</span>
        <div class="step-card">
            <div class="step-card-header">
                <h3 class="step-card-title">{{ $step['title'] }}</h3>
                <span class="step-card-tag">Langkah {{ $step['number'] }}</span>
            </div>
            <div class="step-card-desc">{{ $step['description'] }}</div>
        </div>
    </li>
    @endforeach
</ol>

<a href="{{ route('pelacakan-status') }}" class="progress-cta-card">
    <div class="progress-cta-text">
        <h3><x-icon name="activity" /> Cek Progress Berkas Anda</h3>
        <p>Pantau posisi berkas usulan jabatan fungsional Anda secara real-time — lihat sudah sampai tahap mana prosesnya.</p>
    </div>
    <span class="btn btn--primary progress-cta-btn">Lihat Status <x-icon name="chevron-right" /></span>
</a>

<x-alert type="info" title="Informasi Penting" class="alur-info">
    Seluruh proses pengusulan jabatan fungsional wajib dilakukan melalui unit kerja masing-masing dan dikoordinasikan dengan
    Tim Kerja Pembinaan JF BPS RI Pusat. Untuk pertanyaan lebih lanjut, silakan hubungi tim kami melalui halaman
    <a href="{{ route('tim-kerja') }}">Tim Kerja</a>.
</x-alert>
@endsection
