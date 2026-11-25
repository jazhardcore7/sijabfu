{{-- Dashboard Pegawai (FR-DASH-3): progres lima status, ringkasan status, notifikasi terbaru. --}}
@php $dashUsulan = $dashboard['usulan']; @endphp
<section class="dash-wrap" data-section="dashboard-pegawai">
    <div class="dash-grid">
        <div class="dash-card dash-span-2" data-section="progres-usulan">
            <h2 class="dash-card-title"><x-icon name="activity" /> Progres Usulan Saya</h2>
            @if($dashUsulan)
            <x-tracker class="dash-tracker" :steps="$dashboard['tahapan']" />
            <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $dashboard['persen'] }}" aria-label="Progres usulan">
                <div class="progress-fill" style="width: {{ $dashboard['persen'] }}%;"></div>
            </div>
            <div class="dash-muted dash-persen" data-persen="{{ $dashboard['persen'] }}">{{ $dashboard['persen'] }}% selesai</div>
            <script type="application/json" data-chart-json="progres-pegawai">@json(['status_key' => $dashUsulan->status_key, 'persen' => $dashboard['persen'], 'tahapan' => $dashboard['tahapan']])</script>
            @else
            <x-empty-state icon="file-plus" title="Belum ada usulan" data-section="belum-ada-usulan">
                Belum ada usulan yang dikirim.
                <x-slot:action><a href="{{ route('pengajuan') }}" class="btn btn--primary btn--sm">Ajukan usulan</a></x-slot:action>
            </x-empty-state>
            @endif
        </div>

        <div class="dash-card" data-section="ringkasan-status">
            <h2 class="dash-card-title"><x-icon name="file-text" /> Ringkasan Status</h2>
            @if($dashUsulan)
            <dl class="dash-kv">
                <dt>Status</dt><dd><span class="status-badge" data-status="{{ $dashUsulan->status_key ?: 'draf' }}" data-status-key="{{ $dashUsulan->status_key }}">{{ $dashUsulan->statusKeyLabel() }}</span></dd>
                <dt>Hasil verifikasi</dt><dd><strong>{{ $dashUsulan->hasil_verifikasi ? strtoupper($dashUsulan->hasil_verifikasi) : '-' }}</strong></dd>
                <dt>Jenis usulan</dt><dd><strong>{{ $dashUsulan->jenis_usulan }}</strong></dd>
            </dl>
            @if($dashUsulan->catatan)
            <div class="dash-note">Catatan: {{ $dashUsulan->catatan }}</div>
            @endif
            <a href="{{ route('pelacakan-status') }}" class="link-arrow">Lihat Pelacakan Status <x-icon name="chevron-right" /></a>
            @else
            <div class="dash-muted">-</div>
            @endif
        </div>

        <div class="dash-card" data-section="notifikasi-terbaru">
            <h2 class="dash-card-title"><x-icon name="bell" /> Notifikasi Terbaru</h2>
            @forelse($latestNotifications as $n)
            <div class="dash-notif-item">
                <a href="{{ route('notifikasi.open', $n->id) }}">{{ $n->data['judul'] ?? 'Notifikasi' }}</a>
                <div class="dash-muted">{{ $n->data['pesan'] ?? '' }}</div>
            </div>
            @empty
            <div class="dash-muted">Belum ada notifikasi.</div>
            @endforelse
        </div>
    </div>
</section>
