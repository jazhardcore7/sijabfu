<?php

namespace Tests\Feature\Satker;

use App\Models\FormUsulan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Phase3\UsulanFixtures;
use Tests\TestCase;

/**
 * FR-SATKER-1: role admin_satker, bukan staf. Matriks route S1 (diperluas di S3, FR-SATKER-19).
 */
class RoleAdminSatkerTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    public function test_role_label_and_not_staff(): void
    {
        $admin = User::factory()->adminSatker('32')->create();

        $this->assertSame('Admin Satuan Kerja Provinsi', $admin->levelLabel());
        $this->assertTrue($admin->isAdminSatker());
        $this->assertFalse($admin->isAdmin(), 'admin_satker tidak boleh dianggap staf');
        $this->assertFalse($admin->isPegawai());

        $this->actingAs($admin)->get('/dashboard')->assertOk()
            ->assertSee('data-level="admin_satker"', false)
            ->assertSee('Admin Satuan Kerja Provinsi');
    }

    public static function routes(): array
    {
        // [method, uri, status untuk admin_satker]
        return [
            'dashboard' => ['get', '/dashboard', 200],
            'beranda' => ['get', '/', 302],
            'admin lama' => ['get', '/admin', 403],
            'admin monitoring lama' => ['get', '/admin/monitoring', 403],
            'monitoring' => ['get', '/monitoring', 403],
            'monitoring status' => ['post', '/monitoring/{usulan}/status', 403],
            'verifikasi' => ['get', '/verifikasi', 403],
            'verifikasi detail' => ['get', '/verifikasi/{usulan}', 403],
            'verifikasi keputusan' => ['post', '/verifikasi/{usulan}', 403],
            'pengajuan pegawai' => ['get', '/pengajuan', 403],
            'pengajuan pegawai post' => ['post', '/pengajuan', 403],
            'pelacakan pegawai' => ['get', '/pelacakan-status', 403],
            'regulasi' => ['get', '/regulasi', 200],
            'kelola regulasi' => ['get', '/admin/regulasi', 403],
            'tim kerja' => ['get', '/tim-kerja', 200],
            'messenger' => ['get', '/messenger', 403],
            'alur layanan' => ['get', '/alur-layanan', 200],
            'daftar user' => ['get', '/admin/users', 403],
            'ubah user' => ['post', '/admin/users/1', 403],
            'sso sync' => ['get', '/admin/sso-sync', 403],
            'sso sync run' => ['post', '/admin/sso-sync', 403],
        ];
    }

    #[DataProvider('routes')]
    public function test_admin_satker_route_access(string $method, string $uri, int $status): void
    {
        Storage::fake('local');
        $admin = User::factory()->adminSatker('32')->create();
        // Usulan nyata (diajukan admin ini) agar 403 berasal dari middleware role, bukan 404 route model binding
        $usulan = $this->usulanAt(User::factory()->pegawai()->provinsi('32')->create(), FormUsulan::STATUS_VERIFIKASI, ['submitted_by' => $admin->id]);
        $uri = str_replace('{usulan}', (string) $usulan->id, $uri);

        $this->actingAs($admin)->{$method}($uri, $method === 'post' ? ['aksi' => 'keputusan', 'hasil' => 'ms'] : [])->assertStatus($status);
        $this->assertSame(FormUsulan::STATUS_VERIFIKASI, $usulan->fresh()->status_key, 'status tidak berubah');
    }

    public function test_sidebar_shows_only_admin_satker_menu(): void
    {
        $admin = User::factory()->adminSatker('32')->create();
        $html = $this->actingAs($admin)->get('/dashboard')->getContent();

        $this->assertStringContainsString('data-role="admin_satker"', $html);
        foreach (['dashboard', 'regulasi', 'tim-kerja'] as $route) {
            $this->assertStringContainsString('data-menu-route="'.$route.'"', $html);
        }
        foreach (['pengajuan', 'pelacakan-status', 'verifikasi.index', 'monitoring.index', 'admin.users', 'admin.sso-sync', 'messenger.index', 'admin.regulasi.index'] as $route) {
            $this->assertStringNotContainsString('data-menu-route="'.$route.'"', $html);
        }
    }
}
