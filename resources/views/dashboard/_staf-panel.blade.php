{{-- Grafik Dashboard Tim Pembinaan JF (FR-DASH-4) dan tambahan Super Admin (FR-DASH-5). --}}
@php
    // Warna tetap per status (sama dengan badge dan tracker).
    $statusColors = collect(\App\Models\FormUsulan::STATUS_LABELS)
        ->mapWithKeys(fn ($label, $key) => [$label => "var(--s-{$key}-bar)"])->all();
@endphp
<section class="dash-wrap" data-section="dashboard-grafik">
    <h2 class="dash-section-title"><x-icon name="bar-chart" /> Grafik Usulan</h2>
    <div class="dash-grid">
        @include('dashboard._bar-chart', ['chartId' => 'per-status', 'title' => 'Usulan per Status', 'data' => $charts['status'], 'colors' => $statusColors])
        @include('dashboard._bar-chart', ['chartId' => 'per-jenis', 'title' => 'Usulan per Jenis Usulan', 'data' => $charts['jenis'], 'color' => 'var(--c-primary)'])
        @include('dashboard._bar-chart', ['chartId' => 'per-bulan', 'title' => 'Usulan per Bulan (12 bulan terakhir)', 'data' => $charts['bulan'], 'color' => 'var(--c-primary)'])
    </div>

    @if($isSuperAdmin ?? false)
    <div class="dash-grid dash-grid--spaced">
        {{-- Kartu Sinkronisasi Data SSO (FR-DASH-5, FR-USR-2) --}}
        <div class="dash-card" data-section="sso-sync-card">
            <h3 class="dash-card-title"><x-icon name="refresh" /> Sinkronisasi Data SSO</h3>
            @if($ssoLog)
            <dl class="dash-kv">
                <dt>Terakhir dijalankan</dt><dd><strong data-sso="started">{{ $ssoLog->started_at?->format('d M Y H:i') ?? '-' }}</strong></dd>
                <dt>Selesai</dt><dd><strong data-sso="finished">{{ $ssoLog->finished_at?->format('d M Y H:i') ?? 'Belum selesai' }}</strong></dd>
                <dt>User baru</dt><dd><strong data-sso="created">{{ $ssoLog->created_count }}</strong></dd>
                <dt>User diperbarui</dt><dd><strong data-sso="updated">{{ $ssoLog->updated_count }}</strong></dd>
                <dt>Gagal</dt><dd><strong data-sso="failed">{{ $ssoLog->failed_count }}</strong></dd>
            </dl>
            @if($ssoLog->error)
            <x-alert type="error" class="dash-alert" data-sso="error">Error: {{ $ssoLog->error }}</x-alert>
            @endif
            @else
            <div class="dash-muted" data-sso="empty">Belum pernah sinkronisasi.</div>
            @endif
            <form action="{{ route('admin.sso-sync.run') }}" method="POST" id="form-sso-dashboard">
                @csrf
                <button type="button" class="dash-btn-sync" data-action="sinkronkan" data-modal-open="modal-sso-dashboard"><x-icon name="refresh" /> Sinkronkan</button>
            </form>
            <x-modal id="modal-sso-dashboard" title="Sinkronisasi Data SSO">
                Jalankan sinkronisasi data SSO sekarang?
                <x-slot:footer>
                    <button type="button" class="btn btn--secondary" data-modal-close>Batal</button>
                    <button type="submit" form="form-sso-dashboard" class="btn btn--primary">Sinkronkan</button>
                </x-slot:footer>
            </x-modal>
            <a href="{{ route('admin.sso-sync') }}" class="link-arrow">Lihat riwayat sinkronisasi <x-icon name="chevron-right" /></a>
        </div>

        @include('dashboard._bar-chart', ['chartId' => 'user-per-role', 'title' => 'User per Role', 'data' => $usersPerRole, 'color' => 'var(--s-ditetapkan-bar)'])
    </div>
    @endif
</section>
