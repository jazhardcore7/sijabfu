@php
    use Illuminate\Support\Facades\Route;

    // Menu per role sesuai PRD Revisi bagian 3 (nama menu persis dari PDF).
    // Item yang route-nya belum ada (fitur fase berikutnya) disembunyikan otomatis.
    $menus = [
        'pegawai' => [
            ['label' => 'Dashboard', 'route' => 'home', 'icon' => '🏠'],
            ['label' => 'Pengajuan Usulan', 'route' => 'pengajuan', 'icon' => '📝'],
            ['label' => 'Pelacakan Status', 'route' => 'pelacakan-status', 'icon' => '📊'],
            ['label' => 'Regulasi', 'route' => 'regulasi', 'icon' => '📋'],
            ['label' => 'Tim Kerja', 'route' => 'tim-kerja', 'icon' => '👥'],
            ['label' => 'Chat Box', 'route' => 'chat.index', 'icon' => '💬'],
        ],
        'tim_jf' => [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => '🏠'],
            ['label' => 'Verifikasi Berkas Usulan Pegawai', 'route' => 'verifikasi.index', 'icon' => '🔍'],
            ['label' => 'Monitoring Progress UKOM', 'route' => 'monitoring.index', 'icon' => '📊'],
            ['label' => 'Regulasi', 'route' => 'regulasi', 'icon' => '📋'],
            ['label' => 'Chat Box (Messenger)', 'route' => 'messenger.index', 'icon' => '💬'],
        ],
        'super_admin' => [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => '🏠'],
            ['label' => 'Sinkronisasi Data SSO', 'route' => 'admin.sso-sync', 'icon' => '🔄'],
            ['label' => 'Monitoring Progress UKOM', 'route' => 'monitoring.index', 'icon' => '📊'],
            ['label' => 'Regulasi', 'route' => 'regulasi', 'icon' => '📋'],
            ['label' => 'Daftar User', 'route' => 'admin.users', 'icon' => '👤'],
        ],
    ];

    $role = Auth::user()->role;
    $items = array_filter($menus[$role] ?? [], fn ($item) => Route::has($item['route']));
@endphp
<nav class="sidebar-nav" data-role="{{ $role }}">
    <div class="sidebar-nav-label">Menu Utama</div>
    @foreach($items as $item)
        <a href="{{ route($item['route']) }}"
           class="sidebar-nav-item {{ request()->routeIs($item['route']) ? 'active' : '' }}"
           data-menu-route="{{ $item['route'] }}">
            <div class="nav-icon">{{ $item['icon'] }}</div>
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>
