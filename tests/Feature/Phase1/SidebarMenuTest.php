<?php

namespace Tests\Feature\Phase1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FR-DASH-2: item sidebar per role sesuai PRD bagian 3. Role tidak melihat
 * dan tidak dapat membuka route menu role lain. Item yang route-nya belum
 * dibuat (fase berikutnya) disembunyikan.
 */
class SidebarMenuTest extends TestCase
{
    use RefreshDatabase;

    /** route menu yang tersedia saat ini per role, beserta label PDF */
    private const MENUS = [
        User::ROLE_PEGAWAI => [
            'dashboard' => 'Dashboard',
            'pengajuan' => 'Pengajuan Usulan',
            'pelacakan-status' => 'Pelacakan Status',
            'regulasi' => 'Regulasi',
            'tim-kerja' => 'Tim Kerja',
            'chat.index' => 'Chat Box',
        ],
        User::ROLE_TIM_JF => [
            'dashboard' => 'Dashboard',
            'verifikasi.index' => 'Verifikasi Berkas Usulan Pegawai',
            'monitoring.index' => 'Monitoring Progress UKOM',
            'admin.regulasi.index' => 'Regulasi',
            'messenger.index' => 'Chat Box (Messenger)',
        ],
        User::ROLE_SUPER_ADMIN => [
            'dashboard' => 'Dashboard',
            'admin.sso-sync' => 'Sinkronisasi Data SSO',
            'monitoring.index' => 'Monitoring Progress UKOM',
            'admin.regulasi.index' => 'Regulasi',
            'admin.users' => 'Daftar User',
        ],
    ];

    private function menuRoutesIn(string $html): array
    {
        preg_match_all('/data-menu-route="([^"]+)"/', $html, $m);

        return $m[1];
    }

    public function test_each_role_sees_exactly_its_menu_items(): void
    {
        foreach (self::MENUS as $role => $menu) {
            $user = User::factory()->create(['role' => $role]);
            $uri = $role === User::ROLE_PEGAWAI ? '/regulasi' : '/dashboard';

            $response = $this->actingAs($user)->get($uri)->assertOk();

            $this->assertSame(array_keys($menu), $this->menuRoutesIn($response->getContent()), "Menu role {$role}");
            foreach ($menu as $label) {
                $response->assertSee($label);
            }
        }
    }

    public function test_role_cannot_open_other_roles_menu_routes(): void
    {
        $shared = ['regulasi', 'tim-kerja'];

        foreach (self::MENUS as $role => $_) {
            $user = User::factory()->create(['role' => $role]);
            $own = array_keys(self::MENUS[$role]);

            foreach (self::MENUS as $otherRole => $otherMenu) {
                foreach (array_keys($otherMenu) as $routeName) {
                    // Super Admin boleh membuka semua route Tim JF [ASSUMPTION PRD 11.3]
                    $staffShared = $role === User::ROLE_SUPER_ADMIN && $otherRole === User::ROLE_TIM_JF;
                    if ($staffShared || in_array($routeName, $own, true) || in_array($routeName, $shared, true)) {
                        continue;
                    }

                    $status = $this->actingAs($user)->get(route($routeName))->getStatusCode();

                    // 403, atau redirect ke dashboard sendiri untuk route beranda
                    $this->assertContains($status, [302, 403], "{$role} membuka {$routeName}");
                    if ($status === 302) {
                        $this->assertSame('home', $routeName);
                    }
                }
            }
        }
    }

    public function test_every_pdf_menu_item_now_has_a_route(): void
    {
        // Sejak Fase 8 semua menu PDF punya route; tidak ada item yang disembunyikan.
        foreach (self::MENUS as $menu) {
            foreach (array_keys($menu) as $routeName) {
                $this->assertTrue(\Illuminate\Support\Facades\Route::has($routeName), $routeName);
            }
        }
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('chat.index'));

        // Super Admin tidak melihat menu milik role lain
        $this->actingAs(User::factory()->superAdmin()->create())->get('/dashboard')
            ->assertDontSee('data-menu-route="chat.index"', false)
            ->assertDontSee('data-menu-route="verifikasi.index"', false);
    }
}
