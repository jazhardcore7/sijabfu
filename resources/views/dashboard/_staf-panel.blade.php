{{-- Grafik Dashboard Tim Pembinaan JF (FR-DASH-4) dan tambahan Super Admin (FR-DASH-5). --}}
@include('dashboard._styles')
<section class="dash-wrap" data-section="dashboard-grafik">
    <div class="dash-section-title">📈 Grafik Usulan</div>
    <div class="dash-grid">
        @include('dashboard._bar-chart', ['chartId' => 'per-status', 'title' => 'Usulan per Status', 'data' => $charts['status'], 'color' => 'var(--bps-mid)'])
        @include('dashboard._bar-chart', ['chartId' => 'per-jenis', 'title' => 'Usulan per Jenis Usulan', 'data' => $charts['jenis'], 'color' => 'var(--bps-orange)'])
        @include('dashboard._bar-chart', ['chartId' => 'per-bulan', 'title' => 'Usulan per Bulan (12 bulan terakhir)', 'data' => $charts['bulan'], 'color' => '#16A34A'])
    </div>

    @if($isSuperAdmin ?? false)
    <div class="dash-grid" style="margin-top: 16px;">
        {{-- Kartu Sinkronisasi Data SSO (proses sinkronisasi tersedia di Fase 8) --}}
        <div class="dash-card" data-section="sso-sync-card">
            <div class="dash-card-title">🔄 Sinkronisasi Data SSO</div>
            @if($ssoLog)
            <div class="dash-kv">
                <span>Terakhir dijalankan</span><strong data-sso="started">{{ $ssoLog->started_at?->format('d M Y H:i') ?? '-' }}</strong>
                <span>Selesai</span><strong data-sso="finished">{{ $ssoLog->finished_at?->format('d M Y H:i') ?? 'Belum selesai' }}</strong>
                <span>User baru</span><strong data-sso="created">{{ $ssoLog->created_count }}</strong>
                <span>User diperbarui</span><strong data-sso="updated">{{ $ssoLog->updated_count }}</strong>
                <span>Gagal</span><strong data-sso="failed">{{ $ssoLog->failed_count }}</strong>
            </div>
            @if($ssoLog->error)
            <div class="dash-muted" style="color:#B91C1C; margin-top:8px;" data-sso="error">Error: {{ $ssoLog->error }}</div>
            @endif
            @else
            <div class="dash-muted" data-sso="empty">Belum pernah sinkronisasi.</div>
            @endif
            <button type="button" class="dash-btn-disabled" disabled title="Tersedia setelah modul SSO aktif" data-action="sinkronkan">🔄 Sinkronkan</button>
            <div class="dash-muted" style="margin-top:6px; text-align:center;">Tersedia setelah modul SSO aktif</div>
        </div>

        @include('dashboard._bar-chart', ['chartId' => 'user-per-role', 'title' => 'User per Role', 'data' => $usersPerRole, 'color' => '#7C3AED'])
    </div>
    @endif
</section>
