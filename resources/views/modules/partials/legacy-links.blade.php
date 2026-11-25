{{-- FR-PU-7: tautan lama (link_berkas, link_usulan) hanya tampil read-only bila skemanya http/https. --}}
{{-- Gaya disertakan di partial ini agar tampil sama di halaman Pegawai maupun Verifikasi. --}}
@once
@push('styles')
<style>
    .legacy-card { background: var(--c-surface); border: 1px solid var(--c-border); border-radius: var(--r-lg); overflow: hidden; margin-top: var(--sp-4); }
    .legacy-card-header { display: flex; align-items: center; gap: var(--sp-2); background: var(--c-surface-2); border-bottom: 1px solid var(--c-border); padding: var(--sp-3) var(--sp-5); font-size: var(--fs-sm); font-weight: var(--fw-semibold); color: var(--c-text); }
    .legacy-card-row { padding: var(--sp-3) var(--sp-5); border-bottom: 1px solid var(--c-border-soft); }
    .legacy-card-row:last-child { border-bottom: none; }
    .legacy-card-label { font-size: var(--fs-xs); color: var(--c-text-muted); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: var(--sp-1); }
    .legacy-card-link { font-size: var(--fs-sm); color: var(--c-link); word-break: break-all; }
</style>
@endpush
@endonce
@php
    $legacyBerkas = \App\Models\FormUsulan::safeUrl($usulan->link_berkas);
    $legacyUsulan = \App\Models\FormUsulan::safeUrl($usulan->link_usulan);
@endphp
@if($legacyBerkas || $legacyUsulan)
<div class="legacy-card" data-section="legacy-links">
    <div class="legacy-card-header"><x-icon name="link" /> Tautan Berkas Lama (hanya baca)</div>
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
