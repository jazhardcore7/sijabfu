{{-- FR-PU-7: tautan lama (link_berkas, link_usulan) hanya tampil read-only bila skemanya http/https. --}}
@php
    $legacyBerkas = \App\Models\FormUsulan::safeUrl($usulan->link_berkas);
    $legacyUsulan = \App\Models\FormUsulan::safeUrl($usulan->link_usulan);
@endphp
@if($legacyBerkas || $legacyUsulan)
<div class="pb-detail-card" data-section="legacy-links" style="margin-top:16px;">
    <div class="pb-detail-header">🔗 Tautan Berkas Lama (hanya baca)</div>
    <div class="pb-detail-grid">
        @if($legacyBerkas)
        <div class="pb-detail-row" style="grid-column: 1 / -1;">
            <div class="pb-detail-label">Link Berkas</div>
            <a href="{{ $legacyBerkas }}" target="_blank" rel="noopener noreferrer" class="pb-detail-link">{{ $legacyBerkas }}</a>
        </div>
        @endif
        @if($legacyUsulan)
        <div class="pb-detail-row" style="grid-column: 1 / -1;">
            <div class="pb-detail-label">Link Usulan</div>
            <a href="{{ $legacyUsulan }}" target="_blank" rel="noopener noreferrer" class="pb-detail-link">{{ $legacyUsulan }}</a>
        </div>
        @endif
    </div>
</div>
@endif
