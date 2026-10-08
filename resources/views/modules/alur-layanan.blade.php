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

    .step-bg-blue { background: linear-gradient(135deg, var(--bps-dark), var(--bps-mid)); }
    .step-bg-indigo { background: linear-gradient(135deg, #4F46E5, #7C3AED); }
    .step-bg-orange { background: linear-gradient(135deg, var(--bps-orange), #F59E0B); }
    .step-bg-teal { background: linear-gradient(135deg, #0D9488, #0891B2); }
    .step-bg-green { background: linear-gradient(135deg, #16A34A, #059669); }

    .step-content {
        flex: 1;
    }

    .step-card {
        background: white;
        border-radius: 16px;
        padding: 24px 28px;
        border: 1px solid #e2e8f0;
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
        color: var(--bps-dark);
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

    .tag-blue { background: rgba(0,59,115,0.08); color: var(--bps-dark); }
    .tag-indigo { background: rgba(79,70,229,0.08); color: #4F46E5; }
    .tag-orange { background: rgba(247,127,0,0.1); color: var(--bps-orange); }
    .tag-teal { background: rgba(13,148,136,0.08); color: #0D9488; }
    .tag-green { background: rgba(22,163,74,0.08); color: #16A34A; }

    .step-card-desc {
        font-size: 14.5px;
        color: #475569;
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
</style>
@endpush

@section('content')
<!-- PAGE HEADER -->
<div class="page-header animate-fade-in">
    <h1 class="page-header-title">🔄 Alur Layanan Jabatan Fungsional</h1>
    <p class="page-header-desc">Panduan lengkap proses pengusulan Jabatan Fungsional di lingkungan BPS Republik Indonesia</p>
    <div class="page-header-divider"></div>
</div>

<!-- TIMELINE SUMMARY -->
<div class="timeline-summary animate-fade-in animate-delay-1">
    <div class="timeline-summary-title">📌 Ringkasan Alur Proses</div>
    <div class="timeline-flow">
        <div class="flow-step">📄 Pengusulan</div>
        <div class="flow-arrow">→</div>
        <div class="flow-step">✅ Verifikasi</div>
        <div class="flow-arrow">→</div>
        <div class="flow-step">🎓 Uji Kompetensi</div>
        <div class="flow-arrow">→</div>
        <div class="flow-step">📊 Penetapan PAK</div>
        <div class="flow-arrow">→</div>
        <div class="flow-step">🏅 SK Pengangkatan</div>
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

<!-- INFO BOX -->
<div style="margin-top: 40px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 16px; padding: 24px; max-width: 900px;" class="animate-fade-in">
    <div style="display: flex; gap: 16px; align-items: flex-start;">
        <span style="font-size: 24px; flex-shrink:0;">ℹ️</span>
        <div>
            <div style="font-weight: 700; color: var(--bps-dark); margin-bottom: 8px; font-size: 15px;">Informasi Penting</div>
            <div style="font-size: 14px; color: #374151; line-height: 1.7;">
                Seluruh proses pengusulan Jabatan Fungsional wajib dilakukan melalui unit kerja masing-masing dan dikoordinasikan dengan 
                Tim Kerja Pembinaan JF BPS RI Pusat. Untuk pertanyaan lebih lanjut, silakan hubungi tim kami melalui halaman 
                <a href="{{ route('tim-kerja') }}" style="color: var(--bps-mid); font-weight: 600;">Tim Kerja</a>.
            </div>
        </div>
    </div>
</div>
@endsection
