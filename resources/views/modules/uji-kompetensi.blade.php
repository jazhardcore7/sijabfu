@extends('layouts.app')

@section('title', 'Uji Kompetensi')
@section('breadcrumb', 'Uji Kompetensi')

@push('styles')
<style>
    .uk-grid {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 28px;
        max-width: 1100px;
    }

    /* CTA BANNER */
    .cta-banner {
        background: linear-gradient(135deg, var(--bps-orange) 0%, #F59E0B 50%, #EF4444 100%);
        border-radius: 20px;
        padding: 32px;
        color: white;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .cta-banner::before {
        content: '';
        position: absolute;
        top: -30%;
        right: -20%;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }

    .cta-banner::after {
        content: '';
        position: absolute;
        bottom: -20%;
        left: -10%;
        width: 150px;
        height: 150px;
        background: rgba(255,255,255,0.08);
        border-radius: 50%;
    }

    .cta-banner-icon {
        font-size: 48px;
        margin-bottom: 12px;
        position: relative;
        z-index: 1;
    }

    .cta-banner-title {
        font-size: 20px;
        font-weight: 800;
        margin-bottom: 10px;
        position: relative;
        z-index: 1;
    }

    .cta-banner-desc {
        font-size: 13.5px;
        color: rgba(255,255,255,0.85);
        line-height: 1.6;
        margin-bottom: 24px;
        position: relative;
        z-index: 1;
    }

    .cta-main-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: white;
        color: var(--bps-orange);
        padding: 14px 28px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 800;
        text-decoration: none;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        transition: all 0.3s;
        position: relative;
        z-index: 1;
    }

    .cta-main-btn:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 12px 32px rgba(0,0,0,0.25);
    }

    /* SYARAT CARD */
    .syarat-list { list-style: none; }
    .syarat-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
        color: #374151;
        line-height: 1.5;
    }

    .syarat-item:last-child { border-bottom: none; }

    .syarat-check {
        width: 24px;
        height: 24px;
        background: linear-gradient(135deg, var(--bps-dark), var(--bps-mid));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: white;
        flex-shrink: 0;
        margin-top: 1px;
    }

    /* TAHAPAN TABLE */
    .tahapan-table {
        width: 100%;
        border-collapse: collapse;
    }

    .tahapan-table th {
        background: linear-gradient(135deg, var(--bps-dark), #00509D);
        color: white;
        padding: 14px 18px;
        text-align: left;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.3px;
    }

    .tahapan-table th:first-child { border-radius: 10px 0 0 0; }
    .tahapan-table th:last-child { border-radius: 0 10px 0 0; }

    .tahapan-table td {
        padding: 16px 18px;
        font-size: 14px;
        color: #374151;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: top;
    }

    .tahapan-table tr:last-child td { border-bottom: none; }
    .tahapan-table tr:hover td { background: #f8fafc; }

    .tahapan-number {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, var(--bps-orange), #F59E0B);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 800;
        font-size: 14px;
    }

    @media (max-width: 900px) {
        .uk-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<!-- PAGE HEADER -->
<div class="page-header animate-fade-in">
    <h1 class="page-header-title">🎓 Uji Kompetensi SIGMA JF</h1>
    <p class="page-header-desc">Informasi lengkap mengenai tahapan, syarat, dan portal pelaksanaan Uji Kompetensi JF BPS RI</p>
    <div class="page-header-divider"></div>
</div>

<div class="uk-grid">
    <!-- LEFT COLUMN: Tahapan & Syarat -->
    <div style="display: flex; flex-direction: column; gap: 24px;">

        <!-- TAHAPAN -->
        <div class="card animate-fade-in animate-delay-1">
            <div class="card-header">
                <div style="font-size: 16px; font-weight: 700; color: var(--bps-dark);">📋 Tahapan Uji Kompetensi</div>
                <div style="font-size: 13px; color: #64748b; margin-top: 4px;">Ikuti setiap tahapan secara berurutan</div>
            </div>
            <div style="overflow: hidden; border-radius: 0 0 16px 16px;">
                <table class="tahapan-table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">No.</th>
                            <th>Tahapan</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tahapan as $t)
                        <tr>
                            <td style="text-align: center;">
                                <div class="tahapan-number" style="margin: 0 auto;">{{ $t['no'] }}</div>
                            </td>
                            <td style="font-weight: 700; color: var(--bps-dark); width: 160px;">{{ $t['tahap'] }}</td>
                            <td>{{ $t['keterangan'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SYARAT -->
        <div class="card animate-fade-in animate-delay-2">
            <div class="card-header">
                <div style="font-size: 16px; font-weight: 700; color: var(--bps-dark);">✅ Syarat Peserta Uji Kompetensi</div>
                <div style="font-size: 13px; color: #64748b; margin-top: 4px;">Pastikan Anda memenuhi seluruh syarat berikut</div>
            </div>
            <div class="card-body">
                <ul class="syarat-list">
                    @foreach($syarat as $s)
                    <li class="syarat-item">
                        <div class="syarat-check">✓</div>
                        <span>{{ $s }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN: CTA + Info -->
    <div style="display: flex; flex-direction: column; gap: 24px;">

        <!-- CTA BANNER -->
        <div class="cta-banner animate-fade-in animate-delay-1">
            <div class="cta-banner-icon">🌐</div>
            <div class="cta-banner-title">Portal Uji Kompetensi BPS</div>
            <div class="cta-banner-desc">
                Akses portal resmi SIGMA JF BPS RI untuk mendaftar, memantau status, 
                dan mengikuti ujian secara online.
            </div>
            <a href="https://kompetensi.web.bps.go.id/" target="_blank" rel="noopener noreferrer" class="cta-main-btn">
                🚀 Akses Portal Sekarang
            </a>
        </div>

        <!-- INFO BOX -->
        <div class="card animate-fade-in animate-delay-2">
            <div class="card-header">
                <div style="font-size: 15px; font-weight: 700; color: var(--bps-dark);">ℹ️ Informasi Jadwal</div>
            </div>
            <div class="card-body" style="font-size: 14px; color: #374151; line-height: 1.7;">
                <p style="margin-bottom: 12px;">Jadwal Uji Kompetensi disesuaikan dengan kebutuhan unit kerja dan ditetapkan oleh Tim Kerja Pembinaan JF BPS RI Pusat.</p>
                <div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 10px; padding: 14px; font-size: 13px; color: #92400e;">
                    <strong>⚠️ Perhatian:</strong> Pendaftaran hanya dapat dilakukan melalui portal resmi kompetensi.web.bps.go.id. Pastikan akun Anda sudah terdaftar.
                </div>
            </div>
        </div>

        <!-- CONTACT BOX -->
        <div class="card animate-fade-in animate-delay-3">
            <div class="card-header">
                <div style="font-size: 15px; font-weight: 700; color: var(--bps-dark);">📞 Butuh Bantuan?</div>
            </div>
            <div class="card-body" style="font-size: 14px; color: #374151;">
                <p style="margin-bottom: 12px;">Hubungi Tim Kerja Pembinaan JF BPS RI:</p>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 10px; padding: 10px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <span>✉️</span>
                        <span style="font-size: 13px;">jabatanfungsional@bps.go.id</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; padding: 10px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <span>🏢</span>
                        <span style="font-size: 13px;">Gedung 3 Lt. 2 BPS RI, Jakarta</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
