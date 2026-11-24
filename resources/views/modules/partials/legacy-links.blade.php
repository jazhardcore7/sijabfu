{{-- FR-PU-7: tautan lama (link_berkas, link_usulan) hanya tampil read-only bila skemanya http/https. --}}
{{-- Gaya disertakan di partial ini agar tampil sama di halaman Pegawai maupun Verifikasi. --}}
@once
@push('styles')
<style>
    .legacy-card { background: var(--c-surface); border: 1px solid var(--c-border); border-radius: 16px; overflow: hidden; margin-top: 16px; }
    .legacy-card-header { background: var(--c-surface-2); border-bottom: 1px solid var(--c-border); padding: 12px 20px; font-size: 13.5px; font-weight: 700; color: var(--c-text); }
    .legacy-card-row { padding: 12px 20px; border-bottom: 1px solid var(--c-border-soft); }
    .legacy-card-row:last-child { border-bottom: none; }
    .legacy-card-label { font-size: 11px; color: var(--c-text-faint); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
    .legacy-card-link { font-size: 13px; color: var(--bps-mid); word-break: break-all; }
</style>
@endpush
@endonce
@php
    $legacyBerkas = \App\Models\FormUsulan::safeUrl($usulan->link_berkas);
    $legacyUsulan = \App\Models\FormUsulan::safeUrl($usulan->link_usulan);
@endphp
@if($legacyBerkas || $legacyUsulan)
<div class="legacy-card" data-section="legacy-links">
    <div class="legacy-card-header">🔗 Tautan Berkas Lama (hanya baca)</div>
    @if($legacyBerkas)
    <div class="legacy-card-row">
        <div class="legacy-card-label">Link Berkas</div>
        <a href="{{ $legacyBerkas }}" target="_blank" rel="noopener noreferrer" class="legacy-card-link">{{ $legacyBerkas }}</a>
    </div>
    @endif
    @if($legacyUsulan)
    <div class="legacy-card-row">
        <div class="legacy-card-label">Link Usulan</div>
        <a href="{{ $legacyUsulan }}" target="_blank" rel="noopener noreferrer" class="legacy-card-link">{{ $legacyUsulan }}</a>
    </div>
    @endif
</div>
@endif
