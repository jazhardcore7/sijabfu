@php
    use Illuminate\Support\Facades\Route;

    // Menu per role sesuai PRD Revisi bagian 3 (nama menu persis dari PDF).
    // Item yang route-nya belum ada (fitur fase berikutnya) disembunyikan otomatis.
    $menus = [
        'pegawai' => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'dashboard'],
            ['label' => 'Pengajuan Usulan', 'route' => 'pengajuan', 'icon' => 'file-plus'],
            ['label' => 'Pelacakan Status', 'route' => 'pelacakan-status', 'icon' => 'activity'],
            ['label' => 'Regulasi', 'route' => 'regulasi', 'icon' => 'book'],
            ['label' => 'Tim Kerja', 'route' => 'tim-kerja', 'icon' => 'users'],
            ['label' => 'Chat Box', 'route' => 'chat.index', 'icon' => 'message'],
        ],
        'tim_jf' => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'dashboard'],
            ['label' => 'Verifikasi Berkas Usulan Pegawai', 'route' => 'verifikasi.index', 'icon' => 'search-check'],
            ['label' => 'Monitoring Progress UKOM', 'route' => 'monitoring.index', 'icon' => 'bar-chart'],
            ['label' => 'Regulasi', 'route' => 'admin.regulasi.index', 'icon' => 'book'],
            ['label' => 'Chat Box (Messenger)', 'route' => 'messenger.index', 'icon' => 'message'],
        ],
        // FR-SATKER-1: menu Admin Satuan Kerja Provinsi (nama menu sama dengan Pegawai, isi sesuai cakupannya)
        'admin_satker' => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'dashboard'],
            ['label' => 'Pengajuan Usulan', 'route' => 'satker.pengajuan.index', 'icon' => 'file-plus'],
            ['label' => 'Pelacakan Status', 'route' => 'satker.pelacakan.index', 'icon' => 'activity'],
            ['label' => 'Regulasi', 'route' => 'regulasi', 'icon' => 'book'],
            ['label' => 'Tim Kerja', 'route' => 'tim-kerja', 'icon' => 'users'],
            ['label' => 'Chat Box', 'route' => 'chat.index', 'icon' => 'message'],
        ],
        'super_admin' => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'dashboard'],
            ['label' => 'Sinkronisasi Data SSO', 'route' => 'admin.sso-sync', 'icon' => 'refresh'],
            ['label' => 'Monitoring Progress UKOM', 'route' => 'monitoring.index', 'icon' => 'bar-chart'],
            ['label' => 'Regulasi', 'route' => 'admin.regulasi.index', 'icon' => 'book'],
            ['label' => 'Daftar User', 'route' => 'admin.users', 'icon' => 'user-cog'],
        ],
    ];

    $role = Auth::user()->role;
    $items = array_filter($menus[$role] ?? [], fn ($item) => Route::has($item['route']));

    // FR-SATKER-6: Pengajuan Usulan Pegawai hanya bila boleh mengajukan sendiri
    $items = array_filter($items, fn ($item) => $item['route'] !== 'pengajuan'
        || \Illuminate\Support\Facades\Gate::allows('create', \App\Models\FormUsulan::class));

    // Badge pesan belum dibaca untuk menu chat (FR-CHAT-1, FR-CHAT-2)
    $chatRoutes = ['chat.index', 'messenger.index'];
    $hasChatMenu = collect($items)->contains(fn ($item) => in_array($item['route'], $chatRoutes, true));
    $chatUnread = $hasChatMenu ? app(\App\Services\ChatService::class)->unreadCount(Auth::user()) : 0;
@endphp
<nav class="sidebar-nav" data-role="{{ $role }}">
    <div class="sidebar-nav-label">Menu Utama</div>
    @foreach($items as $item)
        <a href="{{ route($item['route']) }}"
           class="sidebar-nav-item {{ request()->routeIs($item['route']) || request()->routeIs($item['route'] === 'messenger.index' ? 'messenger.*' : (str_starts_with($item['route'], 'satker.') ? \Illuminate\Support\Str::beforeLast($item['route'], '.').'.*' : $item['route'])) ? 'active' : '' }}"
           data-menu-route="{{ $item['route'] }}">
            <div class="nav-icon"><x-icon :name="$item['icon']" /></div>
            {{ $item['label'] }}
            @if(in_array($item['route'], $chatRoutes, true) && $chatUnread > 0)
            <span class="sidebar-unread" data-chat-unread="{{ $chatUnread }}" aria-label="{{ $chatUnread }} pesan belum dibaca">{{ $chatUnread }}</span>
            @endif
        </a>
    @endforeach
</nav>
