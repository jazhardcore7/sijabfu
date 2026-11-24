{{-- Dashboard Pegawai (FR-DASH-3): progres lima status, ringkasan status, notifikasi terbaru. --}}
@include('dashboard._styles')
@php $dashUsulan = $dashboard['usulan']; @endphp
<section class="dash-wrap" data-section="dashboard-pegawai">
    <div class="dash-grid">
        <div class="dash-card" style="grid-column: span 2; min-width: 0;" data-section="progres-usulan">
            <div class="dash-card-title">📊 Progres Usulan Saya</div>
            @if($dashUsulan)
            <div class="dash-steps">
                @foreach($dashboard['tahapan'] as $t)
                <div class="dash-step {{ $t['state'] }}" data-stage="{{ $t['key'] }}" data-state="{{ $t['state'] }}">{{ $t['label'] }}</div>
                @endforeach
            </div>
            <div class="dash-progress"><div style="width: {{ $dashboard['persen'] }}%;"></div></div>
            <div class="dash-muted" style="margin-top:6px;" data-persen="{{ $dashboard['persen'] }}">{{ $dashboard['persen'] }}% selesai</div>
            <script type="application/json" data-chart-json="progres-pegawai">@json(['status_key' => $dashUsulan->status_key, 'persen' => $dashboard['persen'], 'tahapan' => $dashboard['tahapan']])</script>
            @else
            <div class="dash-muted" data-section="belum-ada-usulan">Belum ada usulan yang dikirim. <a href="{{ route('pengajuan') }}" style="color:var(--bps-mid); font-weight:700;">Ajukan usulan</a></div>
            @endif
        </div>

        <div class="dash-card" data-section="ringkasan-status">
            <div class="dash-card-title">📋 Ringkasan Status</div>
            @if($dashUsulan)
            <div class="dash-kv">
                <span>Status</span><strong data-status-key="{{ $dashUsulan->status_key }}">{{ $dashUsulan->statusKeyLabel() }}</strong>
                <span>Hasil verifikasi</span><strong>{{ $dashUsulan->hasil_verifikasi ? strtoupper($dashUsulan->hasil_verifikasi) : '-' }}</strong>
                <span>Jenis usulan</span><strong>{{ $dashUsulan->jenis_usulan }}</strong>
            </div>
            @if($dashUsulan->catatan)
            <div class="dash-muted" style="margin-top:8px;">Catatan: {{ $dashUsulan->catatan }}</div>
            @endif
            <a href="{{ route('pelacakan-status') }}" style="display:inline-block; margin-top:10px; font-size:12.5px; font-weight:700; color:var(--bps-mid); text-decoration:none;">Lihat Pelacakan Status →</a>
            @else
            <div class="dash-muted">-</div>
            @endif
        </div>

        <div class="dash-card" data-section="notifikasi-terbaru">
            <div class="dash-card-title">🔔 Notifikasi Terbaru</div>
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
