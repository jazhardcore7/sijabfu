@extends('layouts.app')

@section('title', 'Beranda')
@section('breadcrumb', 'Dashboard')
@section('flush', '1')

@section('content')
{{-- DASHBOARD PEGAWAI (FR-DASH-3): ditampilkan paling atas --}}
@include('dashboard._pegawai-panel')

{{-- Sambutan ringkas (pengganti hero besar) --}}
<section class="hero-section">
    <div class="hero-inner">
        <div class="hero-text">
            <div class="hero-badge"><x-icon name="shield" /> BPS Republik Indonesia</div>
            <h1 class="hero-title">Portal Layanan <span>SIGMA JF</span> BPS RI Pusat</h1>
            <p class="hero-desc">
                Selamat datang di SIGMA JF Badan Pusat Statistik Republik Indonesia.
                Kami hadir untuk memudahkan proses pengusulan, pembinaan, dan pengembangan
                Jabatan Fungsional di lingkungan BPS RI.
            </p>
        </div>
        <dl class="hero-stats">
            <div class="hero-stat"><dt class="hero-stat-label">Pejabat Fungsional</dt><dd class="hero-stat-number">500+</dd></div>
            <div class="hero-stat"><dt class="hero-stat-label">Jabatan Fungsional</dt><dd class="hero-stat-number">33</dd></div>
            <div class="hero-stat"><dt class="hero-stat-label">Layanan Tersedia</dt><dd class="hero-stat-number">5</dd></div>
            <div class="hero-stat"><dt class="hero-stat-label">Tahun Aktif</dt><dd class="hero-stat-number">2026</dd></div>
        </dl>
    </div>
</section>

{{-- MODUL LAYANAN --}}
<section class="cards-section" aria-labelledby="modul-layanan-title">
    <div class="section-header">
        <div class="section-label"><x-icon name="dashboard" /> Layanan Kami</div>
        <h2 class="section-title" id="modul-layanan-title">5 Modul Layanan Utama</h2>
        <p class="section-desc">Pilih layanan yang Anda butuhkan untuk memulai proses pengusulan dan pembinaan jabatan fungsional</p>
    </div>

    <div class="service-grid">
        <a href="{{ route('alur-layanan') }}" class="service-card">
            <div class="card-icon-wrapper"><x-icon name="route" /></div>
            <div class="card-content">
                <div class="card-title">Alur Layanan</div>
                <div class="card-desc">Panduan step-by-step proses pengusulan Jabatan Fungsional dari awal hingga terbitnya SK Pengangkatan.</div>
            </div>
            <div class="card-arrow">Lihat Alur <x-icon name="chevron-right" /></div>
        </a>

        <a href="{{ route('pengajuan') }}" class="service-card">
            <div class="card-icon-wrapper"><x-icon name="file-plus" /></div>
            <div class="card-content">
                <div class="card-title">Form Usulan</div>
                <div class="card-desc">Formulir pengajuan resmi Jabatan Fungsional secara online. Isi dan kirim dokumen usulan Anda dengan mudah.</div>
            </div>
            <div class="card-arrow">Isi Formulir <x-icon name="chevron-right" /></div>
        </a>

        <a href="{{ route('pelacakan-status') }}" class="service-card">
            <div class="card-icon-wrapper"><x-icon name="activity" /></div>
            <div class="card-content">
                <div class="card-title">Progress Berkas</div>
                <div class="card-desc">Pantau posisi berkas usulan jabatan fungsional Anda secara real-time dari tahap pengusulan hingga selesai.</div>
            </div>
            <div class="card-arrow">Pantau Berkas <x-icon name="chevron-right" /></div>
        </a>

        <a href="{{ route('regulasi') }}" class="service-card">
            <div class="card-icon-wrapper"><x-icon name="book" /></div>
            <div class="card-content">
                <div class="card-title">Regulasi</div>
                <div class="card-desc">Kumpulan peraturan dan regulasi Jabatan Fungsional, termasuk Perka BPS, PermenPAN-RB, dan peraturan BKN.</div>
            </div>
            <div class="card-arrow">Lihat Regulasi <x-icon name="chevron-right" /></div>
        </a>

        <a href="{{ route('tim-kerja') }}" class="service-card">
            <div class="card-icon-wrapper"><x-icon name="users" /></div>
            <div class="card-content">
                <div class="card-title">Tim Kerja</div>
                <div class="card-desc">Profil lengkap Tim Pembinaan JF BPS RI Pusat beserta bidang tanggung jawab masing-masing.</div>
            </div>
            <div class="card-arrow">Lihat Tim <x-icon name="chevron-right" /></div>
        </a>
    </div>
</section>

<footer class="landing-footer">
    <div class="footer-logo">
        <span class="footer-logo-icon">BPS</span>
        <span>Badan Pusat Statistik Republik Indonesia</span>
    </div>
    <div class="footer-copyright">
        Copyright &copy; 2026 Badan Pusat Statistik RI<br>
        Tim Pembinaan JF
    </div>
</footer>
@endsection
