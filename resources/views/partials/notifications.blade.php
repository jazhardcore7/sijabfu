{{-- Lonceng notifikasi (FR-DASH-1, FR-NOT-1): jumlah belum dibaca, 10 terbaru, tandai dibaca. --}}
@php
    $notifUser = Auth::user();
    $notifUnread = $notifUser->unreadNotifications()->count();
    $notifLatest = $notifUser->notifications()->latest()->limit(10)->get();
@endphp
<details class="nav-dropdown" data-layout="notification-bell">
    <summary class="notif-bell-btn" title="Notifikasi" aria-label="Notifikasi ({{ $notifUnread }} belum dibaca)">
        🔔
        <span class="notif-count" data-count="{{ $notifUnread }}">{{ $notifUnread > 99 ? '99+' : $notifUnread }}</span>
    </summary>
    <div class="nav-dropdown-panel notif-panel">
        <div class="notif-panel-head">
            <span style="font-weight: 700;">Notifikasi</span>
            @if($notifUnread > 0)
            <form action="{{ route('notifikasi.baca-semua') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="notif-read-all" data-action="baca-semua">Tandai semua dibaca</button>
            </form>
            @endif
        </div>
        @forelse($notifLatest as $n)
        <a href="{{ route('notifikasi.open', $n->id) }}" class="notif-item {{ $n->read_at ? '' : 'is-unread' }}" data-notification="{{ $n->id }}">
            <div class="notif-item-title">{{ $n->data['judul'] ?? 'Notifikasi' }}</div>
            <div class="notif-item-body">{{ $n->data['pesan'] ?? '' }}</div>
            <div class="notif-item-time">{{ $n->created_at->format('d M Y H:i') }}</div>
        </a>
        @empty
        <div class="notif-empty">Belum ada notifikasi</div>
        @endforelse
    </div>
</details>
