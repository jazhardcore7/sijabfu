{{--
    Detail tracker satu usulan (FR-PS-1..6), dipakai Pelacakan Status Pegawai dan
    Pelacakan Admin Satuan Kerja Provinsi (FR-SATKER-13, FR-SATKER-14).
    Data dari PelacakanController::detailData(); $perbaikanUrl null = tanpa tombol perbaikan (read-only).
--}}
{{-- ─── RINGKASAN STATUS ─── --}}
<div class="pb-hero" data-status-key="{{ $usulan->status_key }}">
    <div class="pb-hero-top">
        <div>
            <div class="pb-hero-label">Nama Pegawai</div>
            <div class="pb-hero-name">{{ $usulan->nama_lengkap }}</div>
            <div class="pb-hero-meta">
                <span class="pb-hero-chip"><x-icon name="user" /> {{ $usulan->jabatan_fungsional }}</span>
                <span class="pb-hero-chip"><x-icon name="file-text" /> {{ $usulan->jenis_usulan }}</span>
                <span class="pb-hero-chip"><x-icon name="calendar" /> Dikirim: {{ ($usulan->submitted_at ?? $usulan->created_at)->format('d M Y') }}</span>
                <span class="pb-hero-chip" data-section="diajukan-oleh"><x-icon name="user" /> Diajukan oleh: {{ $usulan->diajukanOlehLabel() }}</span>
            </div>
        </div>
        <div class="pb-hero-badges">
            <span class="status-badge pb-badge-aktif" data-status="{{ $usulan->status_key ?: 'draf' }}" data-section="status-label">● {{ $usulan->statusKeyLabel() }}</span>
            @if($usulan->hasil_verifikasi)
            {{-- FR-PS-3 --}}
            <span class="hasil-badge {{ $usulan->hasil_verifikasi }}" data-hasil="{{ $usulan->hasil_verifikasi }}">
                <x-icon :name="$usulan->hasil_verifikasi === 'ms' ? 'check-circle' : 'alert-circle'" />
                Hasil Verifikasi: {{ strtoupper($usulan->hasil_verifikasi) }}
            </span>
            @endif
        </div>
    </div>
    <div class="pb-progress-label">
        <span>Progress Keseluruhan</span>
        <span>{{ $persen }}%</span>
    </div>
    <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $persen }}" aria-label="Progress Keseluruhan">
        <div class="progress-fill" style="width: {{ $persen }}%;"></div>
    </div>
</div>

{{-- ─── PERBAIKAN (FR-PS-2) ─── --}}
@if($usulan->status_key === \App\Models\FormUsulan::STATUS_PERBAIKAN)
<x-alert type="warning" title="Perbaikan diperlukan" data-section="perbaikan-banner">
    <div>{{ $usulan->catatan ?: 'Silakan perbaiki berkas yang ditandai.' }}</div>
    @if($flaggedBerkas->isNotEmpty())
    <ul class="alert-list">
        @foreach($flaggedBerkas as $fb)
        <li>{{ $persyaratan[$fb->jenis_persyaratan] ?? $fb->jenis_persyaratan }}{{ $fb->catatan ? ': ' . $fb->catatan : '' }}</li>
        @endforeach
    </ul>
    @endif
    @if($perbaikanUrl)
    <a href="{{ $perbaikanUrl }}" class="btn btn--primary btn--sm alert-action" data-action="perbaiki"><x-icon name="pencil" /> Perbaiki dan Kirim Ulang</a>
    @endif
</x-alert>
@elseif($usulan->catatan)
<x-alert type="info" title="Catatan dari Tim Pembinaan JF" data-section="catatan-terbaru">
    {{ $usulan->catatan }}
</x-alert>
@endif

{{-- ─── TRACKER (FR-PS-1) ─── --}}
<div class="card">
    <div class="card-header"><h2 class="card-title"><x-icon name="route" /> Status Usulan</h2></div>
    <div class="card-body">
        <ol class="pb-lanes">
            @foreach($tahapan as $i => $t)
            @php
                if ($isSelesai || $i < $currentIndex) { $state = 'done'; }
                elseif ($i === $currentIndex) { $state = $usulan->status_key === 'perbaikan' ? 'perbaikan' : 'active'; }
                else { $state = 'pending'; }
                $badgeTxt = match ($state) {
                    'done' => 'Selesai',
                    'active' => 'Sedang Diproses',
                    'perbaikan' => 'Perbaikan',
                    default => 'Belum Mulai',
                };
            @endphp
            <li class="pb-lane" data-stage="{{ $t['key'] }}" data-state="{{ $state }}">
                <span class="pb-step-num">@if($state === 'done')<x-icon name="check" />@else{{ $loop->iteration }}@endif</span>
                <div class="pb-lane-body">
                    <div class="pb-lane-head">
                        <span class="pb-lane-actor-name">{{ $t['label'] }}</span>
                        <span class="pb-step-badge">{{ $badgeTxt }}</span>
                    </div>
                    <div class="pb-step-desc">{{ $t['desc'] }}</div>
                </div>
            </li>
            @endforeach
        </ol>
    </div>
</div>

{{-- ─── DOKUMEN HASIL (FR-PS-4, FR-PS-5) ─── --}}
@if($showSurat || $showSertifikat)
<div class="card card--flush">
    @if($showSurat)
    <div class="doc-row" data-section="surat-usulan">
        <div>
            <div class="doc-title"><x-icon name="file-text" /> Surat Usulan</div>
            <div class="doc-sub">Surat usulan UKOM yang diterbitkan Tim Pembinaan JF</div>
        </div>
        @if($usulan->surat_usulan_path)
        <div class="doc-actions">
            <a class="btn btn--secondary btn--sm" href="{{ route('files.show', ['type' => 'surat', 'id' => $usulan->id, 'mode' => 'preview']) }}" target="_blank" rel="noopener" data-action="preview-surat"><x-icon name="eye" /> Preview</a>
            <a class="btn btn--primary btn--sm" href="{{ route('files.show', ['type' => 'surat', 'id' => $usulan->id]) }}" data-action="download-surat"><x-icon name="download" /> Unduh</a>
        </div>
        @else
        <span class="badge-muted">Dokumen belum diunggah</span>
        @endif
    </div>
    @endif

    @if($showSertifikat)
    <div class="doc-row" data-section="sertifikat-ukom">
        <div>
            <div class="doc-title"><x-icon name="shield" /> Sertifikat UKOM</div>
            @if($usulan->hasil_ukom)
            <div class="doc-sub" data-section="hasil-ukom">Hasil UKOM: {{ $usulan->hasil_ukom }}</div>
            @endif
        </div>
        @if($usulan->sertifikat_path)
        <div class="doc-actions">
            <a class="btn btn--secondary btn--sm" href="{{ route('files.show', ['type' => 'sertifikat', 'id' => $usulan->id, 'mode' => 'preview']) }}" target="_blank" rel="noopener" data-action="preview-sertifikat"><x-icon name="eye" /> Preview</a>
            <a class="btn btn--primary btn--sm" href="{{ route('files.show', ['type' => 'sertifikat', 'id' => $usulan->id]) }}" data-action="download-sertifikat"><x-icon name="download" /> Unduh</a>
        </div>
        @else
        <span class="badge-muted">Dokumen belum diunggah</span>
        @endif
    </div>
    @endif
</div>
@endif

{{-- ─── TIMELINE (FR-PS-1) ─── --}}
<div class="card">
    <div class="card-header"><h2 class="card-title"><x-icon name="clock" /> Riwayat Status</h2></div>
    @if($logs->isEmpty())
    <div class="card-body dash-muted">Belum ada riwayat perubahan status.</div>
    @else
    <ul class="timeline" data-section="timeline">
        @foreach($logs as $log)
        <li data-log-to="{{ $log->to_status }}">
            <div class="timeline-date">{{ $log->created_at->format('d M Y H:i') }}</div>
            <div>
                <div class="timeline-main">
                    {{ $log->from_status ? (\App\Models\FormUsulan::STATUS_LABELS[$log->from_status] ?? $log->from_status) . ' → ' : 'Dikirim → ' }}<strong>{{ \App\Models\FormUsulan::STATUS_LABELS[$log->to_status] ?? $log->to_status }}</strong>
                </div>
                <div class="timeline-actor">oleh {{ $levelLabels[$log->actor?->role] ?? 'Sistem' }}</div>
                @if($log->catatan)
                <div class="timeline-note">{{ $log->catatan }}</div>
                @endif
            </div>
        </li>
        @endforeach
    </ul>
    @endif
</div>

{{-- ─── RINGKASAN USULAN ─── --}}
<div class="card">
    <div class="card-header"><h2 class="card-title"><x-icon name="file-text" /> Ringkasan Usulan</h2></div>
    <dl class="pb-detail-grid">
        <div class="pb-detail-row"><dt class="pb-detail-label">Nama Lengkap</dt><dd class="pb-detail-value">{{ $usulan->nama_lengkap }}</dd></div>
        <div class="pb-detail-row"><dt class="pb-detail-label">NIP</dt><dd class="pb-detail-value">{{ $usulan->nip }}</dd></div>
        <div class="pb-detail-row"><dt class="pb-detail-label">Jabatan Fungsional</dt><dd class="pb-detail-value">{{ $usulan->jabatan_fungsional }}</dd></div>
        <div class="pb-detail-row"><dt class="pb-detail-label">Jenis Usulan</dt><dd class="pb-detail-value">{{ $usulan->jenis_usulan }}</dd></div>
        <div class="pb-detail-row"><dt class="pb-detail-label">Satuan Kerja</dt><dd class="pb-detail-value">{{ $usulan->satuan_kerja }}</dd></div>
        <div class="pb-detail-row"><dt class="pb-detail-label">Email</dt><dd class="pb-detail-value">{{ $usulan->email }}</dd></div>
        <div class="pb-detail-row"><dt class="pb-detail-label">Nomor Surat</dt><dd class="pb-detail-value">{{ $usulan->nomor_surat }}</dd></div>
        <div class="pb-detail-row"><dt class="pb-detail-label">Hal Surat</dt><dd class="pb-detail-value">{{ $usulan->hal_surat }}</dd></div>
    </dl>
</div>

@include('modules.partials.legacy-links', ['usulan' => $usulan])

<div class="pb-help">
    <x-icon name="message" />
    <div>
        <div class="pb-help-title">Ada pertanyaan?</div>
        <div class="pb-help-text">
            Jika ada pertanyaan terkait status usulan Anda, silakan hubungi Tim Kerja Pembinaan JF melalui halaman
            <a href="{{ route('tim-kerja') }}">Tim Kerja</a>.
        </div>
    </div>
</div>
