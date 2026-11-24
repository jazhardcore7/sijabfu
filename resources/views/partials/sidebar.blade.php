@php
    use Illuminate\Support\Facades\Route;

    // Menu per role sesuai PRD Revisi bagian 3 (nama menu persis dari PDF).
    // Item yang route-nya belum ada (fitur fase berikutnya) disembunyikan otomatis.
    $menus = [
        'pegawai' => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => '🏠'],
            ['label' => 'Pengajuan Usulan', 'route' => 'pengajuan', 'icon' => '📝'],
            ['label' => 'Pelacakan Status', 'route' => 'pelacakan-status', 'icon' => '📊'],
            ['label' => 'Regulasi', 'route' => 'regulasi', 'icon' => '📋'],
            ['label' => 'Tim Kerja', 'route' => 'tim-kerja', 'icon' => '👥'],
            ['label' => 'Chat Box', 'route' => 'chat.index', 'icon' => '💬'],
        ],
        'tim_jf' => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => '🏠'],
            ['label' => 'Verifikasi Berkas Usulan Pegawai', 'route' => 'verifikasi.index', 'icon' => '🔍'],
            ['label' => 'Monitoring Progress UKOM', 'route' => 'monitoring.index', 'icon' => '📊'],
            ['label' => 'Regulasi', 'route' => 'admin.regulasi.index', 'icon' => '📋'],
            ['label' => 'Chat Box (Messenger)', 'route' => 'messenger.index', 'icon' => '💬'],
        ],
        'super_admin' => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => '🏠'],
            ['label' => 'Sinkronisasi Data SSO', 'route' => 'admin.sso-sync', 'icon' => '🔄'],
            ['label' => 'Monitoring Progress UKOM', 'route' => 'monitoring.index', 'icon' => '📊'],
            ['label' => 'Regulasi', 'route' => 'admin.regulasi.index', 'icon' => '📋'],
            ['label' => 'Daftar User', 'route' => 'admin.users', 'icon' => '👤'],
        ],
    ];

    $role = Auth::user()->role;
    $items = array_filter($menus[$role] ?? [], fn ($item) => Route::has($item['route']));

    // Badge pesan belum dibaca untuk menu chat (FR-CHAT-1, FR-CHAT-2)
    $chatRoutes = ['chat.index', 'messenger.index'];
    $hasChatMenu = collect($items)->contains(fn ($item) => in_array($item['route'], $chatRoutes, true));
    $chatUnread = $hasChatMenu ? app(\App\Services\ChatService::class)->unreadCount(Auth::user()) : 0;
@endphp
<nav class="sidebar-nav" data-role="{{ $role }}">
    <div class="sidebar-nav-label">Menu Utama</div>
    @foreach($items as $item)
        <a href="{{ route($item['route']) }}"
           class="sidebar-nav-item {{ request()->routeIs($item['route']) || request()->routeIs($item['route'] === 'messenger.index' ? 'messenger.*' : $item['route']) ? 'active' : '' }}"
           data-menu-route="{{ $item['route'] }}">
            <div class="nav-icon">{{ $item['icon'] }}</div>
            {{ $item['label'] }}
            @if(in_array($item['route'], $chatRoutes, true) && $chatUnread > 0)
            <span class="sidebar-unread" data-chat-unread="{{ $chatUnread }}" style="margin-left:auto; min-width:20px; height:20px; padding:0 6px; border-radius:10px; background:#EF4444; color:#FFFFFF; font-size:11px; font-weight:700; display:inline-flex; align-items:center; justify-content:center;">{{ $chatUnread }}</span>
            @endif
        </a>
    @endforeach
</nav>
