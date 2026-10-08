@extends('layouts.app')

@section('title', 'Form Usulan')
@section('breadcrumb', 'Form Usulan')

@push('styles')
<style>
    .form-section {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
        max-width: 1000px;
    }

    .form-info-banner {
        background: linear-gradient(135deg, var(--bps-dark), #00509D);
        border-radius: 20px;
        padding: 24px 28px;
        color: white;
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .form-info-icon {
        width: 56px;
        height: 56px;
        background: rgba(255,255,255,0.15);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        flex-shrink: 0;
    }

    .form-info-content { flex: 1; }
    .form-info-title { font-size: 18px; font-weight: 800; margin-bottom: 6px; }
    .form-info-desc { font-size: 13.5px; color: rgba(255,255,255,0.8); line-height: 1.5; }

    .requirements-list {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 14px;
    }

    .req-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 16px;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }

    .req-item:hover {
        background: #f0f7ff;
        border-color: #93c5fd;
        transform: translateY(-1px);
    }

    .req-icon {
        width: 32px;
        height: 32px;
        background: rgba(0,59,115,0.08);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }

    .req-text { 
        font-size: 13.5px; 
        color: #334155; 
        font-weight: 500;
        line-height: 1.4;
    }

    /* CARD BUKA FORM DI TAB BARU */
    .form-cta-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(0, 59, 115, 0.08);
        padding: 32px 36px;
        position: relative;
        overflow: hidden;
    }

    .form-cta-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, var(--bps-dark), var(--bps-mid), var(--bps-orange));
    }

    .cta-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .cta-badge-official {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: var(--bps-dark);
        border-radius: 20px;
        font-size: 12.5px;
        font-weight: 700;
    }

    .cta-badge-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .cta-status-dot {
        width: 8px;
        height: 8px;
        background: #22c55e;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }

    .cta-body {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 32px;
        align-items: center;
    }

    .cta-title {
        font-size: 22px;
        font-weight: 800;
        color: var(--bps-dark);
        margin-bottom: 10px;
        line-height: 1.3;
    }

    .cta-desc {
        font-size: 14px;
        color: #475569;
        line-height: 1.6;
        margin-bottom: 18px;
    }

    .cta-features {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .cta-feature-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        color: #475569;
        background: #f8fafc;
        padding: 6px 12px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        font-weight: 500;
    }

    .cta-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
        min-width: 290px;
    }

    .btn-main-cta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: linear-gradient(135deg, var(--bps-dark), #00509D);
        color: white;
        padding: 16px 24px;
        border-radius: 14px;
        font-size: 15px;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 4px 16px rgba(0, 59, 115, 0.25);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        text-align: center;
    }

    .btn-main-cta:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 59, 115, 0.35);
        background: linear-gradient(135deg, #002b54, var(--bps-mid));
        color: white;
    }

    .btn-secondary-cta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        color: #334155;
        padding: 11px 18px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        text-align: center;
    }

    .btn-secondary-cta:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
        color: var(--bps-dark);
    }

    .cta-note {
        font-size: 11.5px;
        color: #94a3b8;
        text-align: center;
        margin-top: 4px;
    }

    @media (max-width: 768px) {
        .cta-body {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        .cta-actions {
            min-width: 100%;
        }
        .form-cta-card {
            padding: 24px 20px;
        }
        .requirements-list { 
            grid-template-columns: 1fr; 
        }
    }
</style>
@endpush

@section('content')
<!-- PAGE HEADER -->
<div class="page-header animate-fade-in">
    <h1 class="page-header-title">📝 Form Usulan Jabatan Fungsional</h1>
    <p class="page-header-desc">Ajukan usulan Jabatan Fungsional Anda secara online melalui formulir resmi BPS RI</p>
    <div class="page-header-divider"></div>
</div>

<div class="form-section">
    <!-- INFO BANNER -->
    <div class="form-info-banner animate-fade-in animate-delay-1">
        <div class="form-info-icon">📢</div>
        <div class="form-info-content">
            <div class="form-info-title">Panduan Pengajuan Usulan JF</div>
            <div class="form-info-desc">
                Sebelum mengisi formulir online, pastikan seluruh kelengkapan dokumen persyaratan di bawah ini telah disiapkan dalam format digital (PDF/JPG). Berkas yang lengkap akan mempercepat proses verifikasi oleh Tim Kerja Pembinaan JF BPS RI.
            </div>
        </div>
    </div>

    <!-- 1. PERSYARATAN DOKUMEN (DINAIKKAN KE ATAS) -->
    <div class="card animate-fade-in animate-delay-2" style="background: white; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 2px 12px rgba(0,0,0,0.05); padding: 28px 32px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 6px;">
            <div>
                <div style="font-size: 18px; font-weight: 800; color: var(--bps-dark); display: flex; align-items: center; gap: 8px;">
                    <span>📌</span>
                    <span>Persyaratan Dokumen Usulan</span>
                </div>
                <div style="font-size: 13.5px; color: #64748b; margin-top: 4px;">
                    Siapkan dokumen-dokumen berikut sebelum membuka formulir pengusulan:
                </div>
            </div>
            <span style="font-size: 12px; font-weight: 600; background: #e0f2fe; color: #0369a1; padding: 4px 12px; border-radius: 12px;">
                8 Dokumen Wajib
            </span>
        </div>

        <div class="requirements-list">
            <div class="req-item">
                <div class="req-icon">📄</div>
                <div class="req-text">Fotokopi SK CPNS / PNS terlegalisir</div>
            </div>
            <div class="req-item">
                <div class="req-icon">🎓</div>
                <div class="req-text">Ijazah dan transkrip nilai terlegalisir</div>
            </div>
            <div class="req-item">
                <div class="req-icon">📊</div>
                <div class="req-text">SKP 2 (dua) tahun terakhir bernilai "Baik"</div>
            </div>
            <div class="req-item">
                <div class="req-icon">🆔</div>
                <div class="req-text">Fotokopi KTP / Kartu Identitas ASN</div>
            </div>
            <div class="req-item">
                <div class="req-icon">📋</div>
                <div class="req-text">Surat rekomendasi dari pimpinan unit kerja</div>
            </div>
            <div class="req-item">
                <div class="req-icon">🏆</div>
                <div class="req-text">Sertifikat pelatihan JF yang relevan</div>
            </div>
            <div class="req-item">
                <div class="req-icon">📸</div>
                <div class="req-text">Pas foto terbaru ukuran 4x6 (background merah)</div>
            </div>
            <div class="req-item">
                <div class="req-icon">✍️</div>
                <div class="req-text">DUPAK (Daftar Usul Penetapan Angka Kredit)</div>
            </div>
        </div>

        <div style="margin-top: 18px; padding: 12px 16px; background: #f8fafc; border-radius: 10px; border-left: 4px solid var(--bps-mid); font-size: 13px; color: #475569;">
            💡 <b>Catatan:</b> Pastikan ukuran masing-masing file dokumen tidak melebihi ketentuan pada formulir (maksimal 10 MB per file) dengan penamaan file yang jelas.
        </div>
    </div>

    <!-- 2. CARD BUKA FORM DI TAB BARU (MENGGANTIKAN TAB IFRAME EMBED) -->
    <div class="form-cta-card animate-fade-in animate-delay-3">
        <div class="cta-header">
            <div class="cta-badge-official">
                🏛️ Formulir Resmi BPS RI
            </div>
            <div class="cta-badge-status">
                <div class="cta-status-dot"></div>
                Formulir Google Aktif
            </div>
        </div>

        <div class="cta-body">
            <div>
                <h2 class="cta-title">Usul Pembinaan Jabatan Fungsional</h2>
                <p class="cta-desc">
                    Silakan klik tombol di samping untuk membuka formulir usulan resmi di tab baru. Membuka di tab baru memastikan akun Google / SSO BPS Anda terautentikasi penuh untuk mengunggah berkas persyaratan dan menyimpan progres pengisian secara aman.
                </p>
                <div class="cta-features">
                    <span class="cta-feature-item">📁 Mendukung Unggah Berkas</span>
                    <span class="cta-feature-item">⏱️ Estimasi 10–15 Menit</span>
                    <span class="cta-feature-item">🔒 Akses Akun Resmi</span>
                </div>
            </div>

            <div class="cta-actions">
                <a href="{{ $formUrl }}" target="_blank" rel="noopener noreferrer" class="btn-main-cta">
                    <span>🚀</span>
                    <span>Buka Form di Tab Baru</span>
                    <span>↗</span>
                </a>
                <a href="{{ route('alur-layanan') }}" class="btn-secondary-cta">
                    <span>🔄</span>
                    <span>Lihat Alur Layanan Dulu</span>
                </a>
                <div class="cta-note">
                    *Membuka jendela baru di Google Formulir
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
