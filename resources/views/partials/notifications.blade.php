{{-- Lonceng notifikasi (FR-DASH-1, FR-NOT-1): jumlah belum dibaca, 10 terbaru, tandai dibaca. --}}
@php
    $notifUser = Auth::user();
    $notifUnread = $notifUser->unreadNotifications()->count();
    $notifLatest = $notifUser->notifications()->latest()->limit(10)->get();
@endphp
<details class="nav-dropdown" data-layout="notification-bell">
    <summary class="notif-bell-btn" title="Notifikasi" aria-label="Notifikasi ({{ $notifUnread }} belum dibaca)">
        <x-icon name="bell" />
        <span class="notif-count" data-count="{{ $notifUnread }}">{{ $notifUnread > 99 ? '99+' : $notifUnread }}</span>
    </summary>
    <div class="nav-dropdown-panel notif-panel">
        <div class="notif-panel-head">
            <span>Notifikasi</span>
            @if($notifUnread > 0)
            <form action="{{ route('notifikasi.baca-semua') }}" method="POST">
                @csrf
                <button type="submit" class="notif-read-all" data-action="baca-semua">Tandai semua dibaca</button>
            </form>
            @endif
        </div>
        @forelse($notifLatest as $n)
        <a href="{{ route('notifikasi.open', $n->id) }}" class="notif-item {{ $n->read_at ? '' : 'is-unread' }}" data-notification="{{ $n->id }}">
            <div class="notif-item-title">@unless($n->read_at)<span class="notif-unread-dot" aria-hidden="true"></span><span class="sr-only">Belum dibaca:</span>@endunless{{ $n->data['judul'] ?? 'Notifikasi' }}</div>
            <div class="notif-item-body">{{ $n->data['pesan'] ?? '' }}</div>
            <div class="notif-item-time">{{ $n->created_at->format('d M Y H:i') }}</div>
        </a>
        @empty
        <div class="notif-empty"><x-icon name="inbox" /><div>Belum ada notifikasi</div></div>
        @endforelse
    </div>
</details>
