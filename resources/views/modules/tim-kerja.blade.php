@extends('layouts.app')

@section('title', 'Tim Kerja')
@section('breadcrumb', 'Tim Kerja')

@push('styles')
<style>
    .tim-header-card {
        background: linear-gradient(135deg, var(--bps-dark) 0%, #00509D 60%, var(--bps-mid) 100%);
        border-radius: 20px;
        padding: 36px;
        color: white;
        margin-bottom: 32px;
        display: flex;
        align-items: center;
        gap: 28px;
        position: relative;
        overflow: hidden;
    }

    .tim-header-card::before {
        content: '';
        position: absolute;
        top: -40%;
        right: -10%;
        width: 250px;
        height: 250px;
        background: radial-gradient(circle, rgba(144,224,239,0.12) 0%, transparent 70%);
        pointer-events: none;
    }

    .tim-header-icon {
        width: 80px;
        height: 80px;
        background: rgba(255,255,255,0.15);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        flex-shrink: 0;
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,0.2);
    }

    .tim-header-content { flex: 1; }
    .tim-header-title { font-size: 22px; font-weight: 800; margin-bottom: 8px; }
    .tim-header-desc { font-size: 14px; color: rgba(255,255,255,0.75); line-height: 1.6; max-width: 600px; }

    .tim-stats {
        display: flex;
        gap: 24px;
        margin-top: 16px;
    }

    .tim-stat { text-align: center; }
    .tim-stat-num { font-size: 24px; font-weight: 900; }
    .tim-stat-label { font-size: 11px; color: rgba(255,255,255,0.6); font-weight: 500; }

    /* TEAM GRID */
    .tim-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }

    .tim-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .tim-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.12);
        border-color: transparent;
    }

    .tim-card-top {
        height: 8px;
    }

    .bg-blue { background: linear-gradient(90deg, var(--bps-dark), var(--bps-mid)); }
    .bg-indigo { background: linear-gradient(90deg, #4F46E5, #7C3AED); }
    .bg-orange { background: linear-gradient(90deg, var(--bps-orange), #F59E0B); }
    .bg-teal { background: linear-gradient(90deg, #0D9488, #0891B2); }
    .bg-purple { background: linear-gradient(90deg, #7C3AED, #EC4899); }
    .bg-rose { background: linear-gradient(90deg, #E11D48, #F43F5E); }

    .tim-card-body {
        padding: 28px 24px 24px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .tim-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: 900;
        color: white;
        margin-bottom: 16px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }

    .avatar-blue { background: linear-gradient(135deg, var(--bps-dark), var(--bps-mid)); }
    .avatar-indigo { background: linear-gradient(135deg, #4F46E5, #7C3AED); }
    .avatar-orange { background: linear-gradient(135deg, var(--bps-orange), #F59E0B); }
    .avatar-teal { background: linear-gradient(135deg, #0D9488, #0891B2); }
    .avatar-purple { background: linear-gradient(135deg, #7C3AED, #EC4899); }
    .avatar-rose { background: linear-gradient(135deg, #E11D48, #F43F5E); }

    .tim-nama {
        font-size: 15px;
        font-weight: 800;
        color: var(--bps-dark);
        margin-bottom: 4px;
        line-height: 1.3;
    }

    .tim-nip {
        font-size: 11px;
        color: #94a3b8;
        font-weight: 500;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
        font-family: 'Inter', monospace;
    }

    .tim-jabatan {
        display: inline-flex;
        padding: 5px 14px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .jabatan-ketua {
        background: linear-gradient(135deg, rgba(247,127,0,0.1), rgba(245,158,11,0.1));
        color: var(--bps-orange);
        border: 1px solid rgba(247,127,0,0.3);
    }

    .jabatan-anggota {
        background: rgba(0,59,115,0.08);
        color: var(--bps-dark);
        border: 1px solid rgba(0,59,115,0.15);
    }

    .jabatan-staf {
        background: rgba(13,148,136,0.08);
        color: #0D9488;
        border: 1px solid rgba(13,148,136,0.2);
    }

    .tim-bidang {
        font-size: 12.5px;
        color: #64748b;
        line-height: 1.5;
        background: #f8fafc;
        border-radius: 10px;
        padding: 10px 14px;
        width: 100%;
        border: 1px solid #f1f5f9;
    }

    @media (max-width: 1024px) {
        .tim-grid { grid-template-columns: repeat(2, 1fr); }
        .tim-header-card { flex-direction: column; text-align: center; }
        .tim-stats { justify-content: center; }
    }

    @media (max-width: 640px) {
        .tim-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<!-- PAGE HEADER -->
<div class="page-header animate-fade-in">
    <h1 class="page-header-title">👥 Tim Kerja Pembinaan JF</h1>
    <p class="page-header-desc">Profil lengkap Tim Kerja Pembinaan Jabatan Fungsional BPS RI Pusat</p>
    <div class="page-header-divider"></div>
</div>

<!-- TIM HEADER BANNER -->
<div class="tim-header-card animate-fade-in animate-delay-1">
    <div class="tim-header-icon">👥</div>
    <div class="tim-header-content">
        <div class="tim-header-title">Tim Kerja Pembinaan Jabatan Fungsional</div>
        <div class="tim-header-desc">
            Tim Kerja Pembinaan Jabatan Fungsional BPS RI Pusat bertugas untuk melakukan pembinaan, pengawasan, 
            dan fasilitasi pengusulan serta pengembangan Jabatan Fungsional Statistisi dan Pranata Komputer di 
            seluruh satuan kerja BPS.
        </div>
        <div class="tim-stats">
            <div class="tim-stat">
                <div class="tim-stat-num">{{ count($tim) }}</div>
                <div class="tim-stat-label">Anggota Tim</div>
            </div>
            <div style="width:1px; background:rgba(255,255,255,0.15); height:36px; align-self:center;"></div>
            <div class="tim-stat">
                <div class="tim-stat-num">2</div>
                <div class="tim-stat-label">Jenis JF</div>
            </div>
            <div style="width:1px; background:rgba(255,255,255,0.15); height:36px; align-self:center;"></div>
            <div class="tim-stat">
                <div class="tim-stat-num">34+</div>
                <div class="tim-stat-label">Satker Binaan</div>
            </div>
        </div>
    </div>
</div>

<!-- TIM GRID -->
<div class="tim-grid">
    @foreach($tim as $i => $anggota)
    <div class="tim-card animate-fade-in animate-delay-{{ ($i % 5) + 1 }}">
        <div class="tim-card-top bg-{{ $anggota['color'] }}"></div>
        <div class="tim-card-body">
            <div class="tim-avatar avatar-{{ $anggota['color'] }}">
                {{ $anggota['avatar'] }}
            </div>
            <div class="tim-nama">{{ $anggota['nama'] }}</div>
            <div class="tim-nip">NIP. {{ $anggota['nip'] }}</div>
            <div class="tim-jabatan {{ 
                str_contains($anggota['jabatan'], 'Ketua') ? 'jabatan-ketua' : 
                (str_contains($anggota['jabatan'], 'Staf') ? 'jabatan-staf' : 'jabatan-anggota') 
            }}">
                {{ str_contains($anggota['jabatan'], 'Ketua') ? '⭐ ' : (str_contains($anggota['jabatan'], 'Staf') ? '🗂️ ' : '👤 ') }}{{ $anggota['jabatan'] }}
            </div>
            <div class="tim-bidang">
                📌 {{ $anggota['bidang'] }}
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- CONTACT SECTION -->
<div style="margin-top: 40px; background: white; border-radius: 16px; padding: 28px; border: 1px solid #e2e8f0; max-width: 700px;" class="animate-fade-in">
    <div style="font-size: 16px; font-weight: 700; color: var(--bps-dark); margin-bottom: 16px;">📮 Hubungi Tim Kami</div>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 12px; padding: 14px; background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
            <span style="font-size: 20px;">✉️</span>
            <div>
                <div style="font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Email</div>
                <div style="font-size: 13px; color: #374151; font-weight: 600;">jabatanfungsional@bps.go.id</div>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 12px; padding: 14px; background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
            <span style="font-size: 20px;">📞</span>
            <div>
                <div style="font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Telepon</div>
                <div style="font-size: 13px; color: #374151; font-weight: 600;">(021) 3841195 ext. 3xxxx</div>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 12px; padding: 14px; background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
            <span style="font-size: 20px;">🏢</span>
            <div>
                <div style="font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Alamat</div>
                <div style="font-size: 13px; color: #374151; font-weight: 600;">Gedung 3 Lt. 2, BPS RI, Jakarta</div>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 12px; padding: 14px; background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
            <span style="font-size: 20px;">⏰</span>
            <div>
                <div style="font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Jam Operasional</div>
                <div style="font-size: 13px; color: #374151; font-weight: 600;">Senin–Jumat, 08.00–16.00 WIB</div>
            </div>
        </div>
    </div>
</div>
@endsection
