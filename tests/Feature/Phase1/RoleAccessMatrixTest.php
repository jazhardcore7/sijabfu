<?php

namespace Tests\Feature\Phase1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * FR-RBAC-1: RoleMiddleware menerima beberapa role; matriks role x grup route.
 */
class RoleAccessMatrixTest extends TestCase
{
    use RefreshDatabase;

    public static function matrix(): array
    {
        // [uri, pegawai, tim_jf, super_admin]
        return [
            'admin dashboard'   => ['/admin', 403, 200, 200],
            'admin monitoring'  => ['/admin/monitoring', 403, 302, 302],
            'monitoring'        => ['/monitoring', 403, 200, 200],
            'verifikasi'        => ['/verifikasi', 403, 200, 200],
            'pengajuan usulan'  => ['/pengajuan', 200, 403, 403],
            'pelacakan status'  => ['/pelacakan-status', 200, 403, 403],
            'form usulan lama'  => ['/form-usulan', 302, 403, 403],
            'progress lama'     => ['/progress-berkas', 302, 403, 403],
            'uji kompetensi'    => ['/uji-kompetensi', 302, 403, 403],
            'regulasi'          => ['/regulasi', 200, 200, 200],
            'kelola regulasi'   => ['/admin/regulasi', 403, 200, 200],
            'tim kerja'         => ['/tim-kerja', 200, 200, 200],
            'alur layanan'      => ['/alur-layanan', 200, 200, 200],
            'beranda'           => ['/', 200, 302, 302],
        ];
    }

    #[DataProvider('matrix')]
    public function test_each_role_against_each_route_group(string $uri, int $pegawai, int $timJf, int $superAdmin): void
    {
        $expected = [
            User::ROLE_PEGAWAI => $pegawai,
            User::ROLE_TIM_JF => $timJf,
            User::ROLE_SUPER_ADMIN => $superAdmin,
        ];

        foreach ($expected as $role => $status) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get($uri)->assertStatus($status);
        }
    }

    #[DataProvider('matrix')]
    public function test_guest_is_redirected_to_login(string $uri): void
    {
        $this->get($uri)->assertRedirect(route('login'));
    }

    public function test_staff_home_redirects_to_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->timJf()->create())
            ->get('/')
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_pegawai_cannot_update_status(): void
    {
        $this->actingAs(User::factory()->pegawai()->create())
            ->post('/admin/usulan/1/status', ['status' => 2])
            ->assertForbidden();
    }

    public function test_user_has_role_accepts_several_roles(): void
    {
        $user = User::factory()->superAdmin()->make();

        $this->assertTrue($user->hasRole(User::ROLE_TIM_JF, User::ROLE_SUPER_ADMIN));
        $this->assertFalse($user->hasRole(User::ROLE_PEGAWAI));
    }
}
