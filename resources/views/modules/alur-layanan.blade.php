@extends('layouts.app')

@section('title', 'Alur Layanan')
@section('breadcrumb', 'Alur Layanan')

@push('styles')
<style>
    .steps-container {
        max-width: 900px;
    }

    .step-item {
        display: flex;
        gap: 28px;
        position: relative;
        padding-bottom: 40px;
    }

    .step-item:last-child { padding-bottom: 0; }

    .step-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 27px;
        top: 60px;
        bottom: 0;
        width: 2px;
        background: linear-gradient(180deg, var(--bps-mid) 0%, rgba(0,119,182,0.1) 100%);
    }

    .step-icon-col {
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .step-number-circle {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 900;
        color: white;
        position: relative;
        z-index: 1;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }

    .step-bg-blue   { background: linear-gradient(135deg, var(--bps-dark), var(--bps-mid)); }
    .step-bg-indigo { background: linear-gradient(135deg, #4F46E5, #7C3AED); }
    .step-bg-orange { background: linear-gradient(135deg, var(--bps-orange), #F59E0B); }
    .step-bg-teal   { background: linear-gradient(135deg, #0D9488, #0891B2); }
    .step-bg-purple { background: linear-gradient(135deg, #7C3AED, #A855F7); }
    .step-bg-green  { background: linear-gradient(135deg, #16A34A, #059669); }

    .step-content {
        flex: 1;
    }

    .step-card {
        background: var(--c-surface);
        border-radius: 16px;
        padding: 24px 28px;
        border: 1px solid var(--c-border);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    .step-card:hover {
        transform: translateX(6px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        border-color: var(--bps-light);
    }

    .step-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .step-card-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--c-heading);
    }

    .step-card-tag {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .tag-blue   { background: rgba(0,59,115,0.08);  color: var(--c-heading); }
    .tag-indigo { background: rgba(79,70,229,0.08); color: #4F46E5; }
    .tag-orange { background: rgba(247,127,0,0.1);  color: var(--bps-orange); }
    .tag-teal   { background: rgba(13,148,136,0.08); color: #0D9488; }
    .tag-purple { background: rgba(124,58,237,0.08); color: #7C3AED; }
    .tag-green  { background: rgba(22,163,74,0.08); color: #16A34A; }

    .step-card-desc {
        font-size: 14.5px;
        color: var(--c-text-2);
        line-height: 1.7;
    }

    /* TIMELINE SUMMARY CARD */
    .timeline-summary {
        background: linear-gradient(135deg, var(--bps-dark), #00509D);
        border-radius: 20px;
        padding: 32px;
        color: white;
        margin-bottom: 40px;
    }

    .timeline-summary-title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 20px;
        color: rgba(255,255,255,0.9);
    }

    .timeline-flow {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .flow-step {
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }

    .flow-arrow {
        color: var(--bps-orange);
        font-size: 18px;
        font-weight: bold;
    }

    /* PROGRESS CTA CARD */
    .progress-cta-card {
        background: linear-gradient(135deg, #7C3AED, #4F46E5);
        border-radius: 20px;
        padding: 28px 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        margin-top: 40px;
        max-width: 900px;
        box-shadow: 0 10px 30px rgba(124,58,237,0.25);
        transition: transform 0.3s;
    }
    .progress-cta-card:hover { transform: translateY(-3px); }
    .progress-cta-text h3 { font-size: 20px; font-weight: 800; color: white; margin-bottom: 6px; }
    .progress-cta-text p  { font-size: 14px; color: rgba(255,255,255,0.8); }
    .progress-cta-btn {
        background: var(--c-surface);
        color: #7C3AED;
        padding: 12px 28px;
        border-radius: 12px;
        font-weight: 700;
        text-decoration: none;
        font-size: 14px;
        white-space: nowrap;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .progress-cta-btn:hover { background: #EDE9FE; transform: scale(1.03); }
</style>
@endpush

@section('content')
<!-- PAGE HEADER -->
<div class="page-header animate-fade-in">
    <h1 class="page-header-title">🔄 Alur Layanan SIGMA JF</h1>
    <p class="page-header-desc">Panduan lengkap proses pengusulan jabatan fungsional di lingkungan BPS Republik Indonesia</p>
    <div class="page-header-divider"></div>
</div>

<!-- TIMELINE SUMMARY -->
<div class="timeline-summary animate-fade-in animate-delay-1">
    <div class="timeline-summary-title">📌 Ringkasan Alur Proses</div>
    <div class="timeline-flow">
        <div class="flow-step">🔍 Verifikasi</div>
        <div class="flow-arrow">→</div>
        <div class="flow-step">📄 Surat Usulan Ukom</div>
        <div class="flow-arrow">→</div>
        <div class="flow-step">🏢 Pengelola</div>
        <div class="flow-arrow">→</div>
        <div class="flow-step">🎓 Ukom</div>
        <div class="flow-arrow">→</div>
        <div class="flow-step">🏛️ Instansi Pembina</div>
        <div class="flow-arrow">→</div>
        <div class="flow-step">📢 Pengumuman</div>
    </div>
</div>

<!-- STEPS -->
<div class="steps-container">
    @foreach($steps as $index => $step)
    <div class="step-item animate-fade-in animate-delay-{{ $index + 1 }}">
        <div class="step-icon-col">
            <div class="step-number-circle step-bg-{{ $step['color'] }}">
                {{ $step['number'] }}
            </div>
        </div>
        <div class="step-content">
            <div class="step-card">
                <div class="step-card-header">
                    <div class="step-card-title">{{ $step['title'] }}</div>
                    <div class="step-card-tag tag-{{ $step['color'] }}">
                        Langkah {{ $step['number'] }}
                    </div>
                </div>
                <div class="step-card-desc">{{ $step['description'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- PROGRESS MONITORING CTA -->
<a href="{{ route('pelacakan-status') }}" class="progress-cta-card animate-fade-in">
    <div class="progress-cta-text">
        <h3>📊 Cek Progress Berkas Anda</h3>
        <p>Pantau posisi berkas usulan jabatan fungsional Anda secara real-time — lihat sudah sampai tahap mana prosesnya.</p>
    </div>
    <div class="progress-cta-btn">
        <span>Lihat Status</span>
        <span>→</span>
    </div>
</a>

<!-- INFO BOX -->
<div style="margin-top: 24px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 16px; padding: 24px; max-width: 900px;" class="animate-fade-in">
    <div style="display: flex; gap: 16px; align-items: flex-start;">
        <span style="font-size: 24px; flex-shrink:0;">ℹ️</span>
        <div>
            <div style="font-weight: 700; color: var(--c-heading); margin-bottom: 8px; font-size: 15px;">Informasi Penting</div>
            <div style="font-size: 14px; color: var(--c-text-2); line-height: 1.7;">
                Seluruh proses pengusulan jabatan fungsional wajib dilakukan melalui unit kerja masing-masing dan dikoordinasikan dengan 
                Tim Kerja Pembinaan JF BPS RI Pusat. Untuk pertanyaan lebih lanjut, silakan hubungi tim kami melalui halaman 
                <a href="{{ route('tim-kerja') }}" style="color: var(--bps-mid); font-weight: 600;">Tim Kerja</a>.
            </div>
        </div>
    </div>
</div>
@endsection
