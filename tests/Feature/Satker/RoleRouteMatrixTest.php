<?php

namespace Tests\Feature\Satker;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * FR-SATKER-19: matriks 4 role x route, dengan Pegawai dipecah menjadi Pusat, Daerah, dan tanpa satker.
 * Urutan kolom: [pegawai pusat, pegawai daerah, pegawai tanpa satker, admin satker, tim jf, super admin].
 */
class RoleRouteMatrixTest extends TestCase
{
    use RefreshDatabase;

    private const ACTORS = ['pegawai_pusat', 'pegawai_daerah', 'pegawai_tanpa_satker', 'admin_satker', 'tim_jf', 'super_admin'];

    public static function matrix(): array
    {
        return [
            'dashboard' => ['get', '/dashboard', [200, 200, 200, 200, 200, 200]],
            'beranda' => ['get', '/', [302, 302, 302, 302, 302, 302]],
            'pengajuan pegawai' => ['get', '/pengajuan', [200, 403, 403, 403, 403, 403]],
            'pengajuan pegawai post' => ['post', '/pengajuan', [302, 403, 403, 403, 403, 403]],
            'kirim ulang pegawai' => ['post', '/pengajuan/kirim-ulang', [302, 403, 403, 403, 403, 403]],
            'sinkron pegawai' => ['post', '/pengajuan/sinkron', [302, 403, 403, 403, 403, 403]],
            'form usulan lama post' => ['post', '/form-usulan', [302, 403, 403, 403, 403, 403]],
            'form usulan lama' => ['get', '/form-usulan', [302, 302, 302, 403, 403, 403]],
            'pelacakan pegawai' => ['get', '/pelacakan-status', [200, 200, 200, 403, 403, 403]],
            'progress lama' => ['get', '/progress-berkas', [302, 302, 302, 403, 403, 403]],
            'chat box' => ['get', '/chat', [200, 200, 200, 200, 403, 403]],
            'pengajuan satker' => ['get', '/satker/pengajuan', [403, 403, 403, 200, 403, 403]],
            'pilih pegawai' => ['get', '/satker/pengajuan/pilih-pegawai', [403, 403, 403, 200, 403, 403]],
            'pengajuan satker post' => ['post', '/satker/pengajuan', [403, 403, 403, 302, 403, 403]],
            'pelacakan satker' => ['get', '/satker/pelacakan', [403, 403, 403, 200, 403, 403]],
            'regulasi' => ['get', '/regulasi', [200, 200, 200, 200, 200, 200]],
            'kelola regulasi' => ['get', '/admin/regulasi', [403, 403, 403, 403, 200, 200]],
            'tim kerja' => ['get', '/tim-kerja', [200, 200, 200, 200, 200, 200]],
            'alur layanan' => ['get', '/alur-layanan', [200, 200, 200, 200, 200, 200]],
            'verifikasi' => ['get', '/verifikasi', [403, 403, 403, 403, 200, 200]],
            'monitoring' => ['get', '/monitoring', [403, 403, 403, 403, 200, 200]],
            'messenger' => ['get', '/messenger', [403, 403, 403, 403, 200, 200]],
            'admin lama' => ['get', '/admin', [403, 403, 403, 403, 302, 302]],
            'daftar user' => ['get', '/admin/users', [403, 403, 403, 403, 403, 200]],
            'sso sync' => ['get', '/admin/sso-sync', [403, 403, 403, 403, 403, 200]],
        ];
    }

    private function actor(string $type): User
    {
        $f = User::factory();

        return match ($type) {
            'pegawai_pusat' => $f->pegawai()->pusat()->create(),
            'pegawai_daerah' => $f->pegawai()->provinsi('32')->create(),
            'pegawai_tanpa_satker' => $f->pegawai()->tanpaSatker()->create(),
            'admin_satker' => $f->adminSatker('32')->create(),
            'tim_jf' => $f->timJf()->create(),
            'super_admin' => $f->superAdmin()->create(),
        };
    }

    #[DataProvider('matrix')]
    public function test_role_against_route(string $method, string $uri, array $expected): void
    {
        foreach (self::ACTORS as $i => $type) {
            $this->actingAs($this->actor($type))->{$method}($uri)->assertStatus($expected[$i]);
        }
    }

    #[DataProvider('matrix')]
    public function test_guest_is_redirected_to_login(string $method, string $uri): void
    {
        $this->{$method}($uri)->assertRedirect(route('login'));
    }

    public function test_sidebar_menu_per_actor(): void
    {
        $menus = [
            'pegawai_pusat' => ['dashboard', 'pengajuan', 'pelacakan-status', 'regulasi', 'tim-kerja', 'chat.index'],
            'pegawai_daerah' => ['dashboard', 'pelacakan-status', 'regulasi', 'tim-kerja', 'chat.index'],
            'pegawai_tanpa_satker' => ['dashboard', 'pelacakan-status', 'regulasi', 'tim-kerja', 'chat.index'],
            'admin_satker' => ['dashboard', 'satker.pengajuan.index', 'satker.pelacakan.index', 'regulasi', 'tim-kerja', 'chat.index'],
            'tim_jf' => ['dashboard', 'verifikasi.index', 'monitoring.index', 'admin.regulasi.index', 'messenger.index'],
            'super_admin' => ['dashboard', 'admin.sso-sync', 'monitoring.index', 'admin.regulasi.index', 'admin.users'],
        ];

        foreach ($menus as $type => $routes) {
            $html = $this->actingAs($this->actor($type))->get('/dashboard')->getContent();
            preg_match_all('/data-menu-route="([^"]+)"/', $html, $m);
            $this->assertSame($routes, $m[1], $type);
        }
    }
}
