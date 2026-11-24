<?php

namespace Tests\Feature\Phase1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * FR-DASH-1: semua halaman terautentikasi memuat navbar, lonceng notifikasi,
 * menu profil dengan badge tingkatan, logout, dan tombol tema.
 */
class SharedLayoutTest extends TestCase
{
    use RefreshDatabase;

    public static function pages(): array
    {
        return [
            'pegawai beranda'       => [User::ROLE_PEGAWAI, '/', 'Pegawai'],
            'pegawai pengajuan'     => [User::ROLE_PEGAWAI, '/pengajuan', 'Pegawai'],
            'pegawai pelacakan'     => [User::ROLE_PEGAWAI, '/pelacakan-status', 'Pegawai'],
            'pegawai regulasi'      => [User::ROLE_PEGAWAI, '/regulasi', 'Pegawai'],
            'pegawai tim kerja'     => [User::ROLE_PEGAWAI, '/tim-kerja', 'Pegawai'],
            'pegawai alur layanan'  => [User::ROLE_PEGAWAI, '/alur-layanan', 'Pegawai'],
            'tim jf dashboard'      => [User::ROLE_TIM_JF, '/admin', 'Tim Pembinaan JF'],
            'tim jf monitoring'     => [User::ROLE_TIM_JF, '/monitoring', 'Tim Pembinaan JF'],
            'tim jf verifikasi'     => [User::ROLE_TIM_JF, '/verifikasi', 'Tim Pembinaan JF'],
            'tim jf regulasi'       => [User::ROLE_TIM_JF, '/regulasi', 'Tim Pembinaan JF'],
            'tim jf tim kerja'      => [User::ROLE_TIM_JF, '/tim-kerja', 'Tim Pembinaan JF'],
            'super admin dashboard' => [User::ROLE_SUPER_ADMIN, '/admin', 'Super Admin'],
            'super admin monitoring'=> [User::ROLE_SUPER_ADMIN, '/monitoring', 'Super Admin'],
            'super admin regulasi'  => [User::ROLE_SUPER_ADMIN, '/regulasi', 'Super Admin'],
        ];
    }

    #[DataProvider('pages')]
    public function test_page_renders_the_five_layout_elements(string $role, string $uri, string $level): void
    {
        $user = User::factory()->create(['role' => $role]);

        $response = $this->actingAs($user)->get($uri)->assertOk();

        foreach (['navbar', 'notification-bell', 'profile-menu', 'logout', 'theme-toggle'] as $element) {
            $response->assertSee('data-layout="' . $element . '"', false);
        }

        $response->assertSee('<span class="level-badge" data-level="' . $role . '">' . $level . '</span>', false);
        $response->assertSee(route('logout'), false);
        $this->assertSame(1, substr_count($response->getContent(), 'data-layout="navbar"'));
    }
}
